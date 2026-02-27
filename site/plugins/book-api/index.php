<?php

/**
 * Book Request API Plugin
 * Handles API endpoints for voting and adding books
 */

Kirby::plugin('custom/book-api', [
    'routes' => [
        [
            'pattern' => 'books/vote',
            'method' => 'POST',
            'action' => function() {
                header('Content-Type: application/json');
                $input = json_decode(file_get_contents('php://input'), true);
                $slug = $input['slug'] ?? null;
                
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
                    
                    // Increment votes
                    $currentVotes = (int)$book->votes()->value();
                    kirby()->impersonate('kirby', function() use ($book, $currentVotes) {
                        $book->update(['votes' => $currentVotes + 1]);
                    });
                    
                    http_response_code(200);
                    return ['success' => true, 'votes' => $currentVotes + 1];
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
                    $category = $_POST['category'] ?? 'student';
                    $publisher = $_POST['publisher'] ?? '';
                    $link = $_POST['link'] ?? '';
                    $notes = $_POST['notes'] ?? '';
                    
                    if (empty($title) || empty($suggested_by)) {
                        http_response_code(400);
                        return ['success' => false, 'error' => 'Title and Name are required'];
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
                        'category' => $category,
                        'publisher' => $publisher,
                        'link' => $link,
                        'notes' => $notes,
                        'votes' => 0,
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
