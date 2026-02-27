# 🚀 Running the Book Request Library Locally

## Quick Start (PHP Built-in Server)

### On macOS/Linux:
```bash
cd /Users/raztzur/Documents/GitHub/books
php -S localhost:8000 router.php
```

Then open your browser to: **http://localhost:8000**

### On Windows:
```bash
cd path\to\books
php -S localhost:8000 router.php
```

Then open your browser to: **http://localhost:8000**

---

## What This Does

The command starts a local PHP web server on port 8000 that:
- Serves static files (CSS, JS, images) directly
- Routes all page requests to `index.php`
- Allows Kirby's routing to work properly

---

## Firewall/Port Notes

- If port 8000 is already in use, substitute another port (8001, 8002, etc.)
- If you get "permission denied", you may need `sudo`
- Connection refused means the server didn't start properly

---

## Troubleshooting

### Still seeing directory listing?
- Make sure you're using `router.php` in the command
- Without it: `php -S localhost:8000` (won't work)
- With it: `php -S localhost:8000 router.php` (correct!)

### 404 errors on pages?
- Check that `/content/home/home.txt` exists
- Verify blueprints are in `/site/blueprints/pages/`
- Check browser console (F12) for errors

### Book form not submitting?
- Make sure the server is running
- Check PHP error logs
- Verify `/content/home/` is writable

---

## Production Deployment

For production (Apache, Nginx, etc.):
- Use proper `.htaccess` (for Apache) or Nginx config
- Ensure `/content/`, `/site/` directories are writable
- Set `'debug' => false` in `/site/config/config.php`
- Use HTTPS and set proper permissions

---

## Development Tips

- The server can run in the background using `&`
- To stop: Press `Ctrl+C` in the terminal
- Check `router.php` exists before running command
- File changes are instant, just refresh the browser

**Happy developing! 📚**
