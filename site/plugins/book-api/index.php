<?php

/**
 * Book Request API Plugin
 * Handles API endpoints for voting and adding books
 */

/**
 * Extract Open Graph image from a URL (like Are.na does)
 */
function extractOgImage($url) {
    if (empty($url)) {
        return null;
    }
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    
    try {
        // Set up context with timeout and user agent
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; BookLibrary/1.0)',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        // Fetch the page
        $html = @file_get_contents($url, false, $context);
        if (!$html) {
            return null;
        }
        
        // Try to find og:image
        if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Alternative pattern (content before property)
        if (preg_match('/<meta[^>]*content=["\']([^"\']+)["\'][^>]*property=["\']og:image["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Try twitter:image
        if (preg_match('/<meta[^>]*name=["\']twitter:image["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Alternative pattern for twitter
        if (preg_match('/<meta[^>]*content=["\']([^"\']+)["\'][^>]*name=["\']twitter:image["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }
        
        // Try to find any image with "cover" or "book" in the src
        if (preg_match('/<img[^>]*src=["\']([^"\']*(?:cover|book)[^"\']*)["\'][^>]*>/i', $html, $matches)) {
            $imgUrl = $matches[1];
            // Make relative URLs absolute
            if (strpos($imgUrl, 'http') !== 0) {
                $parsed = parse_url($url);
                $base = $parsed['scheme'] . '://' . $parsed['host'];
                if (strpos($imgUrl, '/') === 0) {
                    $imgUrl = $base . $imgUrl;
                } else {
                    $imgUrl = $base . '/' . $imgUrl;
                }
            }
            return $imgUrl;
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

Kirby::plugin('custom/book-api', [
    'routes' => [
        [
            'pattern' => 'books/vote',
            'method' => 'POST',
            'action' => function() {
                header('Content-Type: application/json');
                $input = json_decode(file_get_contents('php://input'), true);
                $slug = $input['slug'] ?? null;
                $action = $input['action'] ?? 'add';
                
                if (!$slug) {
                    http_response_code(400);
                    return ['success' => false, 'error' => 'Missing slug'];
                }
                
                try {
                    $booksPage = kirby()->page('home');
                    if (!$booksPage) {
                        http_response_code(404);
                        return ['success' => false, 'error' => 'Home page not found'];
                    }
                    
                    $book = $booksPage->children()->findBy('slug', $slug);
                    if (!$book) {
                        http_response_code(404);
                        return ['success' => false, 'error' => 'Book not found'];
                    }
                    
                    // Toggle votes based on action
                    $currentVotes = (int)$book->votes()->value();
                    if ($action === 'remove') {
                        $newVotes = max(0, $currentVotes - 1);
                    } else {
                        $newVotes = $currentVotes + 1;
                    }
                    
                    kirby()->impersonate('kirby', function() use ($book, $newVotes) {
                        $book->update(['votes' => $newVotes]);
                    });
                    
                    http_response_code(200);
                    return ['success' => true, 'votes' => $newVotes];
                } catch (Exception $e) {
                    http_response_code(500);
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }
        ],
        [
            'pattern' => 'books/add',
            'method' => 'POST',
            'action' => function() {
                header('Content-Type: application/json');
                try {
                    $title = $_POST['title'] ?? null;
                    $suggested_by = $_POST['suggested_by'] ?? null;
                    $publisher = $_POST['publisher'] ?? '';
                    $link = $_POST['link'] ?? '';
                    $cover_url = $_POST['cover_url'] ?? '';
                    $notes = $_POST['notes'] ?? '';
                    
                    if (empty($title) || empty($suggested_by)) {
                        http_response_code(400);
                        return ['success' => false, 'error' => 'Title and Name are required'];
                    }
                    
                    // Auto-fetch cover image from URL if not provided
                    if (empty($cover_url) && !empty($link) && empty($_FILES['cover']['tmp_name'])) {
                        $extractedImage = extractOgImage($link);
                        if ($extractedImage) {
                            $cover_url = $extractedImage;
                        }
                    }
                    
                    $booksPage = kirby()->page('home');
                    if (!$booksPage) {
                        http_response_code(404);
                        return ['success' => false, 'error' => 'Home page not found'];
                    }
                    
                    // Create slug
                    $slug = str::slug($title);
                    $slug = $slug ?: 'book-' . time();
                    
                    // Check if book already exists
                    if ($booksPage->children()->findBy('slug', $slug)) {
                        http_response_code(409);
                        return ['success' => false, 'error' => 'Book already exists'];
                    }
                    
                    $data = [
                        'title' => $title,
                        'suggested_by' => $suggested_by,
                        'publisher' => $publisher,
                        'link' => $link,
                        'cover_url' => $cover_url,
                        'notes' => $notes,
                        'votes' => 0,
                        'chosen' => 'false',
                    ];
                    
                    // Create new page (impersonate kirby for permissions)
                    $book = kirby()->impersonate('kirby', function() use ($booksPage, $slug, $data) {
                        $draft = $booksPage->createChild([
                            'slug' => $slug,
                            'template' => 'book',
                            'content' => $data,
                        ]);
                        // Publish the page so it appears in the listing
                        return $draft->changeStatus('listed');
                    });
                    
                    // Handle file upload
                    if (!empty($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['cover'];
                        $tmpPath = $file['tmp_name'];
                        $fileName = $file['name'];
                        
                        // Get file extension
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newFileName = 'cover.' . $ext;
                        
                        // Move file
                        $destPath = $book->root() . '/' . $newFileName;
                        if (move_uploaded_file($tmpPath, $destPath)) {
                            kirby()->impersonate('kirby', function() use ($book, $newFileName) {
                                $book->update(['cover' => $newFileName]);
                            });
                        }
                    }
                    
                    http_response_code(200);
                    return ['success' => true, 'slug' => $slug, 'message' => 'Book added successfully'];
                } catch (Exception $e) {
                    http_response_code(500);
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }
        ]
    ]
]);
