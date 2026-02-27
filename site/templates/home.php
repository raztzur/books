<?php 
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html($page->title()) ?> - Book Request Library</title>
    <link rel="stylesheet" href="<?= url('assets/css/books.css') ?>">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-title">
                <h1><?= html($page->title()) ?></h1>
            </div>
            
            <div class="header-controls">
                <button class="btn-add-request" onclick="openModal('requestForm')">+ Add Request</button>
                
                <div class="controls-group">
                    <div class="sort-control">
                        <label>Sort by:</label>
                        <select id="sortBy" onchange="updateSort(this.value)">
                            <option value="rating" <?= ($kirby->request()->get('sort') ?? 'rating') === 'rating' ? 'selected' : '' ?>>Rating</option>
                            <option value="title" <?= ($kirby->request()->get('sort') ?? '') === 'title' ? 'selected' : '' ?>>A-Z</option>
                            <option value="date" <?= ($kirby->request()->get('sort') ?? '') === 'date' ? 'selected' : '' ?>>Time Added</option>
                        </select>
                    </div>
                    
                    <div class="filter-control">
                        <label>Filter by:</label>
                        <select id="filterBy" onchange="updateFilter(this.value)">
                            <option value="all">All</option>
                            <option value="chosen" <?= ($kirby->request()->get('filter') ?? '') === 'chosen' ? 'selected' : '' ?>>Chosen (Already Ordered)</option>
                            <option value="student" <?= ($kirby->request()->get('filter') ?? '') === 'student' ? 'selected' : '' ?>>Suggested by Students</option>
                            <option value="teacher" <?= ($kirby->request()->get('filter') ?? '') === 'teacher' ? 'selected' : '' ?>>Suggested by Teachers</option>
                        </select>
                    </div>
                    
                    <div class="search-control">
                        <input type="text" id="searchBooks" placeholder="Search books..." value="<?= esc($kirby->request()->get('q') ?? '') ?>" onkeyup="debounceSearch(this.value)">
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="books-gallery">
                <?php 
                $books = $page->children()->listed();
                
                // Sort
                $sort = $kirby->request()->get('sort') ?? 'rating';
                if ($sort === 'title') {
                    $books = $books->sortBy('title', 'asc');
                } elseif ($sort === 'date') {
                    $books = $books->sortBy('date', 'desc');
                } else {
                    // Sort by votes (rating) - highest first
                    $booksArray = [];
                    foreach ($books as $book) {
                        $booksArray[] = $book;
                    }
                    usort($booksArray, function($a, $b) {
                        $aVotes = intval($a->votes()->value() ?? 0);
                        $bVotes = intval($b->votes()->value() ?? 0);
                        return $bVotes <=> $aVotes;
                    });
                    $books = new \Kirby\Toolkit\Collection($booksArray);
                }
                
                // Filter
                $filter = $kirby->request()->get('filter');
                if (!empty($filter) && $filter !== 'all') {
                    $books = $books->filterBy('category', $filter);
                }
                
                // Search
                $search = $kirby->request()->get('q');
                if (!empty($search)) {
                    $books = $books->search($search, ['title', 'suggested_by', 'publisher']);
                }
                
                foreach ($books as $book):
                $suggested = $book->suggested_by()->value() ?? 'Unknown';
                $category = $book->category()->value() ?? 'student';
                $title = $book->title()->value() ?? 'Untitled';
                $publisher = $book->publisher()->value() ?? '';
                $link = $book->link()->value() ?? '';
                $votes = intval($book->votes()->value() ?? 0);
                ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if ($book->cover()->exists()): ?>
                                <?php $cover = $book->cover()->first(); ?>
                                <img src="<?= $cover->url() ?>" alt="<?= esc($title) ?>">
                            <?php else: ?>
                                <div class="cover-placeholder">No Cover</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="book-info">
                            <div class="suggested-by">
                                <div>Suggested by: <strong><?= esc($suggested) ?></strong></div>
                                <span class="category-badge category-<?= esc($category) ?>">
                                    <?= $category === 'student' ? 'Student' : ($category === 'teacher' ? 'Teacher' : 'Chosen') ?>
                                </span>
                            </div>
                            
                            <h2 class="book-title"><?= esc($title) ?></h2>
                            
                            <div class="book-meta">
                                <?php if (!empty($publisher)): ?>
                                    <p class="publisher"><?= esc($publisher) ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($link)): ?>
                                    <a href="<?= esc($link) ?>" target="_blank" class="book-link">View on Site →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="book-footer">
                            <button class="btn-like" onclick="likeBook('<?= $book->slug() ?>')">
                                <span class="heart-icon">♥</span>
                                <span class="vote-count"><?= $votes ?></span>
                            </button>
                        </div>
                    </div>
                <?php 
                endforeach;
                ?>
            </div>
            
            <?php if ($books->count() === 0): ?>
                <div class="no-results">
                    <p>No books found. Try adjusting your filters or search.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Request Modal -->
    <div class="modal" id="requestForm">
        <div class="modal-content">
            <button class="close" onclick="closeModal('requestForm')">&times;</button>
            <h2>Add Book Request</h2>
            
            <form id="addBookForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="suggested_by" required placeholder="Your name">
                </div>
                
                <div class="form-group">
                    <label for="title">Book Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter book title">
                </div>
                
                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" id="publisher" name="publisher" placeholder="Publisher name">
                </div>
                
                <div class="form-group">
                    <label for="link">Book Link</label>
                    <input type="url" id="link" name="link" placeholder="https://goodreads.com/...">
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        <option value="student">Suggested by Student</option>
                        <option value="teacher">Suggested by Teacher</option>
                        <option value="chosen">Already Chosen</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cover">Cover Image</label>
                    <input type="file" id="cover" name="cover" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Any additional information..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        // Handle book submission form
        document.getElementById('addBookForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('/books/add', {
                method: 'POST',
                body: formData
            })
            .then(r => {
                console.log('Response status:', r.status);
                return r.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Data:', data);
                if (data.success) {
                    alert('Book added successfully!');
                    document.getElementById('addBookForm').reset();
                    closeModal('requestForm');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Error: ' + err.message);
            });
        });
        
        const queryParams = new URLSearchParams(window.location.search);
        
        function updateSort(value) {
            queryParams.set('sort', value);
            window.location.search = queryParams.toString();
        }
        
        function updateFilter(value) {
            if (value === 'all') {
                queryParams.delete('filter');
            } else {
                queryParams.set('filter', value);
            }
            window.location.search = queryParams.toString();
        }
        
        let searchTimeout;
        function debounceSearch(value) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (value.trim()) {
                    queryParams.set('q', value);
                } else {
                    queryParams.delete('q');
                }
                window.location.search = queryParams.toString();
            }, 500);
        }
        
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            modal.classList.add('active');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            modal.classList.remove('active');
        }
        
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
                e.target.classList.remove('active');
            }
        });
        
        function likeBook(slug) {
            fetch('/books/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ slug: slug })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>
