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
            <h1 class="page-title"><a href="/"><?= html($firstLine) ?><?php if ($restLines): ?><br><?= html(trim($titleParts[1])) ?><?php endif; ?></a><button class="scroll-top" onclick="scrollToTop()" title="חזרה למעלה">↑</button></h1>
            <button class="btn-add" onclick="openModal('requestForm')">הצעת ספר חדש +</button>
            
            <div class="controls">
                <div class="control-group">
                    <div class="control-label">סדר לפי</div>
                    <div class="control-options">
                        <?php 
                        $currentSort = $kirby->request()->get('sort') ?? 'date';
                        $currentDir = $kirby->request()->get('dir') ?? ($currentSort === 'title' ? 'asc' : 'desc');
                        $filterParam = $kirby->request()->get('filter') ? '&filter=' . $kirby->request()->get('filter') : '';
                        
                        // Toggle direction if clicking same sort
                        function getSortUrl($sort, $currentSort, $currentDir, $filterParam) {
                            if ($sort === $currentSort) {
                                $newDir = $currentDir === 'asc' ? 'desc' : 'asc';
                            } else {
                                $newDir = $sort === 'title' ? 'asc' : 'desc';
                            }
                            return "?sort={$sort}&dir={$newDir}{$filterParam}";
                        }
                        $arrowDown = '<svg class="sort-arrow" width="8" height="8" viewBox="0 0 8 5"><path d="M1 1l3 3 3-3" stroke="currentColor" stroke-width="1" fill="none" /></svg>';
                        $arrowUp = '<svg class="sort-arrow" width="8" height="8" viewBox="0 0 8 5"><path d="M1 4l3-3 3 3" stroke="currentColor" stroke-width="1" fill="none" /></svg>';
                        ?>
                        <a href="<?= getSortUrl('date', $currentSort, $currentDir, $filterParam) ?>" class="control-option <?= $currentSort === 'date' ? 'active' : '' ?>">תאריך העלאה<?php if($currentSort === 'date'): ?>&nbsp;&nbsp;<?= $currentDir === 'desc' ? $arrowDown : $arrowUp ?><?php endif; ?></a>
                        <a href="<?= getSortUrl('rating', $currentSort, $currentDir, $filterParam) ?>" class="control-option <?= $currentSort === 'rating' ? 'active' : '' ?>">פופולריות<?php if($currentSort === 'rating'): ?>&nbsp;&nbsp;<?= $currentDir === 'desc' ? $arrowDown : $arrowUp ?><?php endif; ?></a>
                        <a href="<?= getSortUrl('title', $currentSort, $currentDir, $filterParam) ?>" class="control-option <?= $currentSort === 'title' ? 'active' : '' ?>">אלפבית<?php if($currentSort === 'title'): ?>&nbsp;&nbsp;<?= $currentDir === 'asc' ? $arrowDown : $arrowUp ?><?php endif; ?></a>
                    </div>
                </div>
                
                <div class="control-group">
                    <div class="control-label">סנן לפי <?php $currentFilter = $kirby->request()->get('filter'); if ($currentFilter): ?><a href="?sort=<?= $currentSort ?>&dir=<?= $currentDir ?>" class="show-all">(איפוס)</a><?php endif; ?></div>
                    <div class="control-options">
                        <a href="?sort=<?= $currentSort ?>&dir=<?= $currentDir ?>&filter=student" class="control-option <?= $currentFilter === 'student' ? 'active' : '' ?>">הצעות תלמידים</a>
                        <a href="?sort=<?= $currentSort ?>&dir=<?= $currentDir ?>&filter=teacher" class="control-option <?= $currentFilter === 'teacher' ? 'active' : '' ?>">הצעות מרצים</a>
                                                <a href="?sort=<?= $currentSort ?>&dir=<?= $currentDir ?>&filter=chosen" class="control-option <?= $currentFilter === 'chosen' ? 'active' : '' ?>">הוזמנו</a>

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
            $dir = $kirby->request()->get('dir') ?? ($sort === 'title' ? 'asc' : 'desc');
            
            if ($sort === 'title') {
                $books = $books->sortBy('title', $dir);
            } elseif ($sort === 'date') {
                $books = $books->sortBy('date', $dir);
            } else {
                $booksArray = [];
                foreach ($books as $book) {
                    $booksArray[] = $book;
                }
                usort($booksArray, function($a, $b) use ($dir) {
                    $aVotes = intval($a->votes()->value() ?? 0);
                    $bVotes = intval($b->votes()->value() ?? 0);
                    return $dir === 'desc' ? ($bVotes <=> $aVotes) : ($aVotes <=> $bVotes);
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
            <div class="modal-header">
                <h2>הצעת ספר חדש</h2>
                <button class="modal-close" onclick="closeModal('requestForm')">×</button>
            </div>
            
            <form id="addBookForm" novalidate>
                <div class="field">
                    <input type="text" name="title" required placeholder="שם הספר *">
                </div>
                
                <div class="field">
                    <input type="text" name="publisher" required placeholder="מוציא לאור *">
                </div>
                
                <div class="field">
                    <input type="url" name="link" id="bookLinkInput" required placeholder="קישור לעמוד הספר *">
                </div>
                
                <div class="field">
                    <div class="dropzone" id="coverDropzone">
                        <div class="dropzone-content">
                            <span class="dropzone-text">התמונה תתעדכן אוטומטית מהקישור.<br>לא מרוצים? גררו תמונה או <u>בחרו קובץ</u></span>
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
                    <input type="text" name="suggested_by" required placeholder="השם שלך *">
                </div>
                <div class="field">
                    <div class="role-switch">
                        <input type="radio" name="role" value="student" id="roleStudent" checked>
                        <label for="roleStudent">סטודנט/ית</label>
                        <input type="radio" name="role" value="tutor" id="roleTutor">
                        <label for="roleTutor">מרצה</label>
                    </div>
                </div>
                <div class="field">
                    <!-- <label>הערות</label> -->
                    <textarea name="notes" placeholder="למה צריך את הספר הזה?"></textarea>
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
                    <h2><a href="" target="_blank" id="bookDetailTitleLink"><span id="bookDetailTitle"></span> <span class="arrow">↗</span></a></h2>
                    <p class="book-detail-publisher" id="bookDetailPublisher"></p>
                    <p class="book-detail-meta" id="bookDetailMeta"></p>
                    <p class="book-detail-notes" id="bookDetailNotes"></p>
                    
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
                
                <form id="addCommentForm" class="add-comment-form" novalidate>
                    <input type="hidden" id="commentBookSlug">
                    <div class="field">
                        <input type="text" name="author" placeholder="השם שלך" required>
                    </div>
                    <div class="field">
                        <textarea name="text" placeholder="כתוב תגובה..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">שלח</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textareas
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        document.querySelectorAll('.field textarea').forEach(textarea => {
            textarea.addEventListener('input', () => autoResizeTextarea(textarea));
            autoResizeTextarea(textarea);
        });
        
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
        let autoFetchedImage = false;
        
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
            autoFetchedImage = false;
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
        
        // Auto-fetch cover from book link
        const bookLinkInput = document.getElementById('bookLinkInput');
        let fetchTimeout = null;
        
        bookLinkInput.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            
            // Clear previous timeout
            if (fetchTimeout) {
                clearTimeout(fetchTimeout);
            }
            
            // Don't fetch if user already uploaded/selected an image
            if (dropzone.classList.contains('has-preview') && !autoFetchedImage) {
                return;
            }
            
            // Debounce - wait 500ms after user stops typing
            if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                fetchTimeout = setTimeout(() => {
                    fetch('/books/fetch-cover', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url: url })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.image) {
                            coverUrlHidden.value = data.image;
                            showPreview(data.image);
                            autoFetchedImage = true;
                        }
                    })
                    .catch(() => {});
                }, 500);
            }
        });
        
        // Track when user manually uploads/selects image
        dropzoneFile.addEventListener('change', () => {
            autoFetchedImage = false;
        });
        
        // Clear invalid state on input
        document.querySelectorAll('#addBookForm input[required], #addBookForm textarea[required]').forEach(input => {
            input.addEventListener('input', () => {
                input.closest('.field').classList.remove('invalid', 'shake');
            });
        });
        
        // Form submit
        document.getElementById('addBookForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            let isValid = true;
            const requiredFields = this.querySelectorAll('input[required], textarea[required]');
            
            requiredFields.forEach(input => {
                const field = input.closest('.field');
                field.classList.remove('invalid', 'shake');
                
                if (!input.value.trim()) {
                    isValid = false;
                    field.classList.add('invalid', 'shake');
                    
                    // Remove shake class after animation
                    setTimeout(() => field.classList.remove('shake'), 400);
                }
            });
            
            if (!isValid) return;
            
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
                        
                        // Title link
                        const titleLink = document.getElementById('bookDetailTitleLink');
                        if (book.link) {
                            titleLink.href = book.link;
                            titleLink.style.pointerEvents = '';
                        } else {
                            titleLink.href = '#';
                            titleLink.style.pointerEvents = 'none';
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
                list.innerHTML = '';
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
            
            const modalVoteBtn = document.getElementById('bookDetailVoteBtn');
            const isVoted = votedBooks.includes(currentBookSlug);
            
            // Toggle vote
            toggleVote(currentBookSlug);
            
            // Update modal button state
            if (isVoted) {
                modalVoteBtn.classList.remove('voted');
            } else {
                modalVoteBtn.classList.add('voted');
            }
            
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
