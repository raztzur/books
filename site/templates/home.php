<?php 
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= str_replace('|', ' ', html($page->title())) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/books.css') ?>">
</head>
<body>
    <header class="page-header">
        <div class="header-grid">
            <?php 
            $titleParts = explode('|', $page->title()->value());
            $firstLine = trim($titleParts[0] ?? '');
            $restLines = isset($titleParts[1]) ? '<br>' . trim($titleParts[1]) : '';
            ?>
            <h1 class="page-title"><a href="/"><?= html($firstLine) ?></a><button class="scroll-top" onclick="scrollToTop()" title="חזרה למעלה">↑</button><?php if ($restLines): ?><br><a href="/"><?= html(trim($titleParts[1])) ?></a><?php endif; ?></h1>
            <button class="btn-add" onclick="openModal('requestForm')">הצעת ספר +</button>
            
            <div class="controls">
                <div class="control-group">
                    <div class="control-label">סדר לפי</div>
                    <div class="control-options">
                        <?php $currentSort = $kirby->request()->get('sort') ?? 'date'; ?>
                        <a href="?sort=date<?= $kirby->request()->get('filter') ? '&filter=' . $kirby->request()->get('filter') : '' ?>" class="control-option <?= $currentSort === 'date' ? 'active' : '' ?>">תאריך העלאה</a>
                        <a href="?sort=rating<?= $kirby->request()->get('filter') ? '&filter=' . $kirby->request()->get('filter') : '' ?>" class="control-option <?= $currentSort === 'rating' ? 'active' : '' ?>">פופולריות</a>
                        <a href="?sort=title<?= $kirby->request()->get('filter') ? '&filter=' . $kirby->request()->get('filter') : '' ?>" class="control-option <?= $currentSort === 'title' ? 'active' : '' ?>">אלףבית</a>
                    </div>
                </div>
                
                <div class="control-group">
                    <div class="control-label">סנן לפי <?php $currentFilter = $kirby->request()->get('filter'); if ($currentFilter): ?><a href="?sort=<?= $currentSort ?>" class="show-all">(איפוס)</a><?php endif; ?></div>
                    <div class="control-options">
                        <a href="?sort=<?= $currentSort ?>&filter=chosen" class="control-option <?= $currentFilter === 'chosen' ? 'active' : '' ?>">הוזמנו</a>
                        <a href="?sort=<?= $currentSort ?>&filter=student" class="control-option <?= $currentFilter === 'student' ? 'active' : '' ?>">הצעות תלמידים</a>
                        <a href="?sort=<?= $currentSort ?>&filter=teacher" class="control-option <?= $currentFilter === 'teacher' ? 'active' : '' ?>">הצעות מורים</a>
                    </div>
                </div>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchBooks" placeholder="חיפוש" value="<?= esc($kirby->request()->get('q') ?? '') ?>">
                <?php if ($kirby->request()->get('q')): ?>
                <button type="button" class="search-clear" onclick="clearSearch()">×</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="cards-grid">
            <?php 
            $books = $page->children()->listed();
            
            // Sort
            $sort = $kirby->request()->get('sort') ?? 'date';
            if ($sort === 'title') {
                $books = $books->sortBy('title', 'asc');
            } elseif ($sort === 'date') {
                $books = $books->sortBy('date', 'desc');
            } else {
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
            if ($filter === 'chosen') {
                $books = $books->filterBy('chosen', 'true');
            } elseif ($filter === 'student') {
                $books = $books->filterBy('role', 'student');
            } elseif ($filter === 'teacher') {
                $books = $books->filterBy('role', 'teacher');
            }
            
            // Search
            $search = $kirby->request()->get('q');
            if (!empty($search)) {
                $books = $books->search($search, ['title', 'suggested_by', 'publisher']);
            }
            
            foreach ($books as $book):
                $title = $book->title()->value() ?? 'Untitled';
                $suggested = $book->suggested_by()->value() ?? '';
                $publisher = $book->publisher()->value() ?? '';
                $link = $book->link()->value() ?? '';
                $coverUrl = $book->cover_url()->value() ?? '';
                $votes = intval($book->votes()->value() ?? 0);
                $isChosen = $book->chosen()->value() === 'true';
                $commentsCount = $book->comments()->toStructure()->count();
            ?>
            <article class="card<?= $isChosen ? ' card--chosen' : '' ?>" data-slug="<?= $book->slug() ?>" onclick="openBookModal('<?= $book->slug() ?>')">
                <div class="card-image">
                    <?php if ($book->cover()->toFiles()->count() > 0): ?>
                        <?php $cover = $book->cover()->toFiles()->first(); ?>
                        <img src="<?= $cover->url() ?>" alt="<?= esc($title) ?>">
                    <?php elseif (!empty($coverUrl)): ?>
                        <img src="<?= esc($coverUrl) ?>" alt="<?= esc($title) ?>" onerror="this.parentElement.innerHTML='<div class=\'placeholder\'></div>'">
                    <?php else: ?>
                        <div class="placeholder"></div>
                    <?php endif; ?>
                    <?php if ($isChosen): ?>
                        <span class="badge">הוזמן</span>
                    <?php endif; ?>
                </div>
                
                <div class="card-content">
                    <div class="card-header">
                        <h2 class="card-title"><?= esc($title) ?><?php if (!empty($link)): ?> <a href="<?= esc($link) ?>" target="_blank" class="arrow" onclick="event.stopPropagation()">↖</a><?php endif; ?></h2>
                        <div class="card-actions">
                            <div class="vote-wrap">
                                <button class="btn-vote" data-slug="<?= $book->slug() ?>" onclick="event.stopPropagation(); toggleVote('<?= $book->slug() ?>')">
                                    <span class="heart">♥</span>
                                </button>
                                <span class="vote-count"><?= $votes ?></span>
                            </div>
                            <div class="comment-indicator" data-slug="<?= $book->slug() ?>">
                                <span class="comment-icon">💬</span>
                                <span class="comment-count"><?= $commentsCount ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($publisher)): ?>
                        <p class="card-publisher"><?= esc($publisher) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($suggested)): ?>
                        <p class="card-meta">הוצע ע״י <?= esc($suggested) ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
            
            <?php if ($books->count() === 0): ?>
            <div class="empty">
                <p>סליחה, לא מצאנו ספרים התואמים את הקריטריונים שלך ;(</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal" id="requestForm">
        <div class="modal-box">
            <button class="modal-close" onclick="closeModal('requestForm')">×</button>
            <h2>הוספת ספר</h2>
            
            <form id="addBookForm">
                <div class="field">
                    <label>שם הספר *</label>
                    <input type="text" name="title" required placeholder="שם הספר">
                </div>
                
                <div class="field">
                    <label>השם שלך *</label>
                    <input type="text" name="suggested_by" required placeholder="השם שלך">
                </div>
                
                <div class="field">
                    <label>מוציא לאור</label>
                    <input type="text" name="publisher" placeholder="שם המוציא לאור">
                </div>
                
                <div class="field">
                    <label>קישור לספר</label>
                    <input type="url" name="link" placeholder="קישור מחנות אונליין (התמונה תילקח אוטומטית)">
                </div>
                
                <div class="field">
                    <label>תמונת כריכה (אופציונלי)</label>
                    <div class="dropzone" id="coverDropzone">
                        <div class="dropzone-content">
                            <span class="dropzone-text">גרור קובץ, הדבק קישור לתמונה,<br><u>בחר קובץ</u> או לחץ <u>לבחירה</u></span>
                            <div class="dropzone-preview hidden">
                                <img src="" alt="Preview">
                                <button type="button" class="dropzone-remove">×</button>
                            </div>
                        </div>
                        <input type="file" name="cover" accept="image/*" class="dropzone-file">
                        <input type="hidden" name="cover_url" id="coverUrlHidden">
                    </div>
                </div>
                
                <div class="field">
                    <label>הערות</label>
                    <textarea name="notes" placeholder="למה אתה רוצה את הספר הזה?"></textarea>
                </div>
                
                <button type="submit" class="btn-submit">שלח</button>
            </form>
        </div>
    </div>

    <!-- Book Detail Modal -->
    <div class="modal" id="bookDetailModal">
        <div class="modal-box modal-box--large">
            <button class="modal-close" onclick="closeModal('bookDetailModal')">×</button>
            
            <div class="book-detail">
                <div class="book-detail-cover">
                    <img src="" alt="" id="bookDetailCover">
                    <div class="placeholder" id="bookDetailPlaceholder"></div>
                </div>
                
                <div class="book-detail-info">
                    <h2 id="bookDetailTitle"></h2>
                    <p class="book-detail-publisher" id="bookDetailPublisher"></p>
                    <p class="book-detail-meta" id="bookDetailMeta"></p>
                    <p class="book-detail-notes" id="bookDetailNotes"></p>
                    <a href="" target="_blank" class="book-detail-link" id="bookDetailLink">קישור לספר ↖</a>
                    
                    <div class="book-detail-actions">
                        <div class="vote-wrap">
                            <button class="btn-vote" id="bookDetailVoteBtn" onclick="toggleVoteInModal()">
                                <span class="heart">♥</span>
                            </button>
                            <span class="vote-count" id="bookDetailVotes"></span>
                        </div>
                        <span class="badge" id="bookDetailBadge">הוזמן</span>
                    </div>
                </div>
            </div>
            
            <div class="comments-section">
                <h3>תגובות (<span id="commentsCount">0</span>)</h3>
                
                <div class="comments-list" id="commentsList">
                    <!-- Comments will be loaded here -->
                </div>
                
                <form id="addCommentForm" class="add-comment-form">
                    <input type="hidden" id="commentBookSlug">
                    <div class="comment-inputs">
                        <input type="text" name="author" placeholder="השם שלך" required>
                        <textarea name="text" placeholder="כתוב תגובה..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">שלח</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Scroll to top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        window.addEventListener('scroll', () => {
            const scrollTop = document.querySelector('.scroll-top');
            if (window.scrollY > 300) {
                scrollTop.classList.add('visible');
            } else {
                scrollTop.classList.remove('visible');
            }
        });
        
        // Voted books stored in localStorage
        const votedBooks = JSON.parse(localStorage.getItem('votedBooks') || '[]');
        
        // Mark already voted books
        document.querySelectorAll('.btn-vote').forEach(btn => {
            const slug = btn.dataset.slug;
            if (votedBooks.includes(slug)) {
                btn.classList.add('voted');
            }
        });
        
        function toggleVote(slug) {
            const btn = document.querySelector(`.btn-vote[data-slug="${slug}"]`);
            const isVoted = votedBooks.includes(slug);
            
            fetch('/books/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slug: slug, action: isVoted ? 'remove' : 'add' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const countEl = btn.parentElement.querySelector('.vote-count');
                    countEl.textContent = data.votes;
                    
                    if (isVoted) {
                        const idx = votedBooks.indexOf(slug);
                        votedBooks.splice(idx, 1);
                        btn.classList.remove('voted');
                    } else {
                        votedBooks.push(slug);
                        btn.classList.add('voted');
                    }
                    localStorage.setItem('votedBooks', JSON.stringify(votedBooks));
                }
            });
        }
        
        // Search
        function clearSearch() {
            const params = new URLSearchParams(window.location.search);
            params.delete('q');
            window.location.search = params.toString();
        }
        
        let searchTimeout;
        document.getElementById('searchBooks').addEventListener('keyup', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const params = new URLSearchParams(window.location.search);
                if (this.value.trim()) {
                    params.set('q', this.value);
                } else {
                    params.delete('q');
                }
                window.location.search = params.toString();
            }, 800);
        });
        
        // Modal
        function openModal(id) {
            document.getElementById(id).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            document.body.style.overflow = '';
        }
        
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal(modal.id);
            });
        });
        
        // Dropzone functionality (Are.na style)
        const dropzone = document.getElementById('coverDropzone');
        const dropzoneFile = dropzone.querySelector('.dropzone-file');
        const dropzoneText = dropzone.querySelector('.dropzone-text');
        const dropzonePreview = dropzone.querySelector('.dropzone-preview');
        const dropzoneImg = dropzonePreview.querySelector('img');
        const dropzoneRemove = dropzone.querySelector('.dropzone-remove');
        const coverUrlHidden = document.getElementById('coverUrlHidden');
        
        function showPreview(src) {
            dropzoneImg.src = src;
            dropzoneText.classList.add('hidden');
            dropzonePreview.classList.remove('hidden');
            dropzone.classList.add('has-preview');
        }
        
        function clearPreview() {
            dropzoneImg.src = '';
            dropzoneText.classList.remove('hidden');
            dropzonePreview.classList.add('hidden');
            dropzone.classList.remove('has-preview');
            dropzoneFile.value = '';
            coverUrlHidden.value = '';
        }
        
        // Click to select file
        dropzone.addEventListener('click', (e) => {
            if (e.target !== dropzoneRemove && !dropzone.classList.contains('has-preview')) {
                dropzoneFile.click();
            }
        });
        
        // File selected
        dropzoneFile.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => showPreview(e.target.result);
                reader.readAsDataURL(file);
            }
        });
        
        // Remove button
        dropzoneRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            clearPreview();
        });
        
        // Drag and drop
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                // Create a new FileList-like object
                const dt = new DataTransfer();
                dt.items.add(file);
                dropzoneFile.files = dt.files;
                
                const reader = new FileReader();
                reader.onload = (ev) => showPreview(ev.target.result);
                reader.readAsDataURL(file);
            }
        });
        
        // Paste image URL or image from clipboard
        dropzone.addEventListener('paste', (e) => {
            e.preventDefault();
            
            // Check for pasted image
            const items = e.clipboardData.items;
            for (let item of items) {
                if (item.type.startsWith('image/')) {
                    const file = item.getAsFile();
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    dropzoneFile.files = dt.files;
                    
                    const reader = new FileReader();
                    reader.onload = (ev) => showPreview(ev.target.result);
                    reader.readAsDataURL(file);
                    return;
                }
            }
            
            // Check for pasted URL
            const text = e.clipboardData.getData('text');
            if (text && (text.startsWith('http://') || text.startsWith('https://'))) {
                // Check if it looks like an image URL
                if (text.match(/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i)) {
                    coverUrlHidden.value = text;
                    showPreview(text);
                }
            }
        });
        
        // Make dropzone focusable for paste
        dropzone.setAttribute('tabindex', '0');
        
        // Form submit
        document.getElementById('addBookForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('/books/add', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    clearPreview();
                    closeModal('requestForm');
                    location.reload();
                } else {
                    alert(data.error || 'שגיאה בשליחת הבקשה');
                }
            })
            .catch(err => alert('שגיאה: ' + err.message));
        });
        
        // Book Detail Modal
        let currentBookSlug = null;
        
        function openBookModal(slug) {
            currentBookSlug = slug;
            document.getElementById('commentBookSlug').value = slug;
            
            // Fetch book details
            fetch(`/books/${slug}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const book = data.book;
                        
                        // Populate modal
                        document.getElementById('bookDetailTitle').textContent = book.title;
                        document.getElementById('bookDetailPublisher').textContent = book.publisher || '';
                        document.getElementById('bookDetailPublisher').style.display = book.publisher ? '' : 'none';
                        document.getElementById('bookDetailMeta').textContent = book.suggested_by ? `הוצע ע״י ${book.suggested_by}` : '';
                        document.getElementById('bookDetailMeta').style.display = book.suggested_by ? '' : 'none';
                        document.getElementById('bookDetailNotes').textContent = book.notes || '';
                        document.getElementById('bookDetailNotes').style.display = book.notes ? '' : 'none';
                        document.getElementById('bookDetailVotes').textContent = book.votes;
                        
                        // Link
                        const linkEl = document.getElementById('bookDetailLink');
                        if (book.link) {
                            linkEl.href = book.link;
                            linkEl.style.display = '';
                        } else {
                            linkEl.style.display = 'none';
                        }
                        
                        // Cover image
                        const coverImg = document.getElementById('bookDetailCover');
                        const placeholder = document.getElementById('bookDetailPlaceholder');
                        if (book.cover) {
                            coverImg.src = book.cover;
                            coverImg.style.display = '';
                            placeholder.style.display = 'none';
                        } else {
                            coverImg.style.display = 'none';
                            placeholder.style.display = '';
                        }
                        
                        // Badge
                        document.getElementById('bookDetailBadge').style.display = book.chosen ? '' : 'none';
                        
                        // Vote button state
                        const voteBtn = document.getElementById('bookDetailVoteBtn');
                        voteBtn.dataset.slug = slug;
                        if (votedBooks.includes(slug)) {
                            voteBtn.classList.add('voted');
                        } else {
                            voteBtn.classList.remove('voted');
                        }
                        
                        // Load comments
                        loadComments(book.comments);
                        
                        openModal('bookDetailModal');
                    }
                });
        }
        
        function loadComments(comments) {
            const list = document.getElementById('commentsList');
            document.getElementById('commentsCount').textContent = comments.length;
            
            if (comments.length === 0) {
                list.innerHTML = '<p class="no-comments">אין תגובות עדיין. היה הראשון להגיב!</p>';
                return;
            }
            
            list.innerHTML = comments.map(c => `
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author">${escapeHtml(c.author)}</span>
                        <span class="comment-date">${c.date}</span>
                    </div>
                    <p class="comment-text">${escapeHtml(c.text)}</p>
                </div>
            `).join('');
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function toggleVoteInModal() {
            if (!currentBookSlug) return;
            toggleVote(currentBookSlug);
            
            // Update modal vote count after a short delay
            setTimeout(() => {
                const card = document.querySelector(`.card[data-slug="${currentBookSlug}"]`);
                if (card) {
                    const voteCount = card.querySelector('.vote-count').textContent;
                    document.getElementById('bookDetailVotes').textContent = voteCount;
                }
            }, 300);
        }
        
        // Add comment form
        document.getElementById('addCommentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const slug = document.getElementById('commentBookSlug').value;
            const author = this.querySelector('[name="author"]').value;
            const text = this.querySelector('[name="text"]').value;
            
            fetch(`/books/${slug}/comment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ author, text })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Clear form
                    this.querySelector('[name="text"]').value = '';
                    
                    // Reload book details to get updated comments
                    fetch(`/books/${slug}`)
                        .then(r => r.json())
                        .then(bookData => {
                            if (bookData.success) {
                                loadComments(bookData.book.comments);
                                
                                // Update comment count on card
                                const indicator = document.querySelector(`.comment-indicator[data-slug="${slug}"] .comment-count`);
                                if (indicator) {
                                    indicator.textContent = data.totalComments;
                                }
                            }
                        });
                } else {
                    alert(data.error || 'שגיאה בשליחת התגובה');
                }
            })
            .catch(err => alert('שגיאה: ' + err.message));
        });
    </script>
</body>
</html>
