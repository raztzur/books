/**
 * Helper functions for the book library
 */

// Helper to return JSON responses
if (!function_exists('json_response')) {
    function json_response($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Helper to create URL-friendly slugs
if (!function_exists('slug')) {
    function slug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}
