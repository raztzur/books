# 🚀 Book Request Library - Quick Start Guide

## ⚡ First: Run the Local Server

```bash
cd /Users/raztzur/Documents/GitHub/books
php -S localhost:8000 router.php
```

Then open: **http://localhost:8000**

(If port 8000 is busy, use 8001, 8002, etc.)

---

## Getting Started

### 1️⃣ Access the Library
Open your browser and go to:
```
http://localhost:8000
```

(Replace `localhost:8000` with your actual domain)

### 2️⃣ What You'll See
A beautiful gallery of books with:
- Book covers and titles
- Who suggested each book
- Category badges (Student, Teacher, Chosen)
- Publisher information
- Vote counts
- Search, filter, and sort options

### 3️⃣ Finding Books
Use the controls at the top:

**Search Field** - Type keywords:
- Book title: "Design"
- Author name: "Sarah"
- Publisher: "Phaidon"

**Filter Dropdown** - Select a category:
- All books
- Only student suggestions
- Only teacher suggestions  
- Only chosen books

**Sort Dropdown** - Choose order:
- Rating (most liked first)
- A-Z (alphabetical)
- Time Added (newest first)

### 4️⃣ Voting on Books
Click the **♥ button** on any book you like. The count increases immediately!

### 5️⃣ Submitting a Book Request

1. Click the **+ Add Request** button
2. Fill in your details:
   - **Name*** - Who is this suggestion from?
   - **Title*** - What's the book called?
   - **Publisher** - Who published it? (optional)
   - **Link** - Goodreads URL? (optional)
   - **Category*** - Who's suggesting?
   - **Cover Image** - Upload a picture (optional)
   - **Notes** - Why you want it (optional)
3. Click **Submit Request**
4. ✅ Done! Your book appears in the gallery

\* Required fields

---

## 📱 Mobile Users

The library works perfectly on phones and tablets! All features (search, filter, sort, voting, submitting) work on mobile.

---

## 🛠️ Admin Features

### Access the Admin Panel
```
http://localhost:8000/panel
```

### What You Can Do
- **View all books** in organized table
- **See vote counts** at a glance
- **Edit book details** if needed
- **See statistics** on submissions
- **Manage categories** to identify trends
- **Sort by popularity** to see what's most wanted

### Table Columns
- Title - Book name
- Suggested By - Who submitted it
- Category - Student/Teacher/Chosen
- Votes - ♥ count
- Publisher - Publishing company

---

## 🎯 Common Tasks

### I want to see the most popular books
1. Make sure **Sort by: Rating** is selected
2. Books are automatically ordered by votes (highest first)

### I want to find all student suggestions
1. Click **Filter by: Suggested by Students**
2. Only books suggested by students appear

### I want to search for a specific book
1. Type in the search box at the top
2. Results appear as you type
3. Searches title, publisher, and suggester name

### I want to mark a book as "Already Chosen"
1. Go to admin Panel (`/panel/`)
2. Edit the book's category to "Chosen"
3. It now appears under "Chosen (Already Ordered)"

### I want to upload a cover image
1. Click **+ Add Request** or edit an existing book
2. Click "Browse" in the **Cover Image** field
3. Select a JPG or PNG file
4. Submit the form
5. Image appears on the book card

---

## ❓ FAQ

**Q: Do I need to create an account to suggest a book?**
A: No! Just enter your name as part of the suggestion.

**Q: Can I vote twice for the same book?**
A: Currently yes - there's no vote restriction. This could be changed if needed.

**Q: Where does the data get saved?**
A: Everything is stored as files on the server. Books are in `/content/books/`.

**Q: Can I delete a book suggestion?**
A: Only admins can delete books through the Panel. Email an admin if needed.

**Q: What if my book already exists in the list?**
A: You can still suggest it - it creates a duplicate. Consider voting on the existing one instead!

**Q: How do I contact an admin?**
A: Check the home page for contact information.

---

## 🎨 The Design

### Colors Mean
- 🟢 **Green button** - Action buttons like "Add Request"
- 💜 **Purple badge** - Student suggestion
- 🔴 **Red badge** - Teacher suggestion
- 🟢 **Green badge** - Already chosen
- ❤️ **Red heart** - Vote button

### Cards Show
**Top section**: Cover image (or placeholder)

**Middle section**:
- Who suggested it
- Category badge
- Book title
- Publisher name
- Link to external site

**Bottom section**: Heart button with vote count

---

## 💡 Tips

1. **Search is live** - Type to see results instantly
2. **Refresh page to see votes** - After voting, the page updates automatically
3. **Multiple categories help** - Filter wisely to find what you need
4. **Sort options change** - Try different sort orders to discovery books
5. **Cover images are helpful** - Adding images makes books more discoverable
6. **Notes are useful** - Explain WHY you want the book!

---

## 🆘 Troubleshooting

### Books aren't showing up
- Check your home page URL (should be `/` or your domain root)
- Try refreshing the page
- Check browser console for errors (F12)

### Can't submit a book
- Make sure you filled in all required fields (marked with *)
- Check that file upload isn't too large (if adding cover)

### Vote button isn't working
- Try refreshing the page
- Check JavaScript is enabled
- Try a different browser

### Admin Panel won't load
- Correct URL is `/panel/` (with trailing slash)
- Make sure cookies are enabled

---

## 📖 Full Documentation

For more details, see:
- [BOOK_LIBRARY_README.md](BOOK_LIBRARY_README.md) - Complete feature guide
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - Technical overview

---

**Ready to explore? Visit your home page now! 📚**
