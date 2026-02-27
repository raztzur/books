# 📚 Book Request Library

A modern, interactive book request management system built with Kirby CMS. Community members can submit book suggestions, vote on favorites, and browse the collection. Teachers and admins can manage requests and track purchase decisions.

## Features

### 👥 User Features
- **Browse Books** - View the entire book collection in a beautiful gallery
- **Search** - Find books by title, publisher, or suggester
- **Filter** - Filter by category (Student Suggestions, Teacher Suggestions, Already Chosen)
- **Sort** - Sort by Rating, Alphabetically, or Date Added
- **Vote** - Like favorite books with the heart button
- **Submit Requests** - No authentication needed - just submit your name and book details
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile

### 🎯 Admin Features (Panel Back Office)
- **Statistics Dashboard** - View key metrics at a glance
- **Book Management** - Edit, view, and organize all requests
- **Voting Insights** - See which books are most popular
- **Category Organization** - Easily identify who suggested what
- **Bulk Actions** - Manage multiple books efficiently

## Site Structure

```
/content/books/                 # Main books collection
  /1_anatomy-of-a-design...    # Sample book 1
  /2_blick/                     # Sample book 2
  /3_punk-print/                # Sample book 3
  books.txt                      # Books page content
  
/site/
  blueprints/
    pages/
      books.yml                 # Books collection blueprint
      book.yml                  # Individual book blueprint
  templates/
    books.html                  # Books gallery view
    book.html                   # Individual book detail
  controllers/
    books.php                   # Books controller
  plugins/
    book-api.php               # API endpoints for voting and adding
  config/
    config.php                 # Main configuration
```

## How to Submit a Book Request

1. Click the **"+ Add Request"** button at the top of the page
2. Fill in the form with:
   - **Your Name** (required)
   - **Book Title** (required)
   - **Publisher** (optional)
   - **Book Link** (optional - link to Goodreads, Amazon, etc.)
   - **Category** - Choose who's suggesting:
     - Student
     - Teacher
     - Chosen (Already Decided to Order)
   - **Cover Image** (optional)
   - **Notes** (optional - why you want this book, etc.)
3. Click **"Submit Request"**
4. Your book will appear in the gallery immediately!

## How to Vote/Like Books

Click the heart (♥) button on any book card to vote for it. The vote count increases and helps track which books are most popular.

## Sorting & Filtering

### Sort By:
- **Rating** - Most popular books first (based on votes)
- **A-Z** - Alphabetical order by title
- **Time Added** - Newest requests first

### Filter By:
- **All** - Show everything
- **Chosen** - Books already decided to order
- **Suggested by Students** - Student suggestions
- **Suggested by Teachers** - Teacher suggestions

### Search:
Use the search field to find books by:
- Title
- Author/Suggester name
- Publisher

## Book Information Displayed

Each book card shows:
- **Cover Image** - Visual book cover
- **Title** - Book's title
- **Suggested By** - Who suggested it (name)
- **Category Badge** - Color-coded category indicator
- **Publisher** - Publishing company
- **Book Link** - Link to external resource
- **Vote Count** - Number of likes/votes

## Admin Dashboard (Panel)

Access the Panel at `/panel/` to:

1. **View Statistics** - Total requests, breakdown by category
2. **Manage Books** - Edit details, check votes, organize requests
3. **Create Entries** - Manually add books (if needed)
4. **Review Data** - See who suggested what and how popular each book is

The Panel displays books in a table view sorted by votes, making it easy to see what students and teachers want most.

## Categories Explained

### 👨‍🎓 Student Suggestions
Students can submit books they'd like the library to have. These represent student interests and reading preferences.

### 👨‍🏫 Teacher Suggestions  
Teachers submit books for curriculum, enrichment, or reference. Often weighted more heavily in purchase decisions.

### ✓ Chosen
Books that have already been approved for purchase or are already in the collection. These are shown as finalized decisions.

## Technical Details

### Technologies Used
- **Kirby 4** - Modern, flexible file-based CMS
- **PHP** - Backend logic
- **HTML5/CSS3** - Frontend interface
- **JavaScript** - Interactive features (voting, filtering, search)
- **JSON** - API communication

### API Endpoints

#### Vote on a Book
```
POST /api/books/vote
Content-Type: application/json

{
  "slug": "book-title-slug"
}
```

Response:
```json
{
  "success": true,
  "votes": 13
}
```

#### Add a New Book
```
POST /api/books
Content-Type: multipart/form-data

Form Fields:
- title (required)
- suggested_by (required)
- category (student|teacher|chose)
- publisher
- link
- notes
- cover (file upload)
```

Response:
```json
{
  "success": true,
  "slug": "book-title-slug",
  "message": "Book added successfully"
}
```

## Customization

### Styling
Edit `/assets/css/books.css` to customize:
- Colors (CSS variables at the top)
- Layout and spacing
- Category badge colors
- How things look on different screen sizes

### Blueprint Fields
Edit `/site/blueprints/pages/book.yml` to add new fields or modify existing ones:
- Change field types
- Add required fields
- Adjust field labels
- Add field descriptions

### Settings
Edit `/site/config/config.php` to modify:
- Debug mode (set to `false` in production)
- YAML parser
- Other Kirby options

## Content Format

Books are stored as folders with a `book.txt` file. Example structure:

```
/1_book-title/
  book.txt              # Book metadata
  cover.jpg             # Book cover image (if uploaded)
```

The `book.txt` file uses this format:
```
Title: The Book Title

----

Suggested_by: Student Name

----

Category: student

----

Publisher: Publisher Name

----

Link: https://goodreads.com/...

----

Votes: 5

----

Notes: Some notes about this book
```

## Performance Tips

- Keep cover images optimized (under 500KB)
- Don't add too many high-resolution images
- Use the search feature for large collections
- The Panel sorts by votes for easy management

## Troubleshooting

### "Books page not found"
Make sure the `/content/books/` directory exists and contains a `books.txt` file.

### Images not showing up
- Check file permissions
- Verify image format is supported (JPG, PNG, WebP)
- Make sure file upload is under size limit

### API errors
- Check browser console for error messages
- Verify all required fields in form
- Ensure file uploads are under server limit

### Voting not working
- Clear browser cache
- Check that JavaScript is enabled
- Verify API endpoint is accessible

## Future Enhancements

Potential features to add:
- User authentication for voting (prevent double votes)
- Email notifications for new suggestions
- Advanced analytics and reports
- Book availability integration
- Social sharing features
- Average rating calculation
- Comments/reviews section
- Wishlist features

## License

This book request library is built on Kirby CMS. See Kirby's license for details.

---

**Happy reading! 📖**
