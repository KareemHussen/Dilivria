# Image Handling in DSITO Project

This document explains how images are stored and accessed in the DSITO project.

## Storage Structure

Images are stored using Laravel's public disk through the `store()` method. Here's how it works:

1. When a user uploads an image (e.g., a profile picture), it's stored in:
   ```
   storage/app/public/profile/[filename].jpg
   ```

2. Laravel's storage:link creates a symbolic link from:
   ```
   public/storage -> storage/app/public
   ```
   
   This makes the files accessible via web URLs.

## Accessing Images

To display an image in your frontend, you should use the `picture_url` attribute that's automatically added to User models:

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "picture": "profile/abc123.jpg",
    "picture_url": "http://yourdomain.com/storage/profile/abc123.jpg"
  }
}
```

## Best Practices

1. **Storing images**:
   ```php
   // Use this pattern
   $imagePath = $request->file('picture')->store('profile', 'public');
   ```
   
2. **Retrieving image URLs**:
   ```php
   // In your frontend:
   const imageUrl = user.picture_url;
   ```

## Troubleshooting

If images are not displaying:

1. Check that `php artisan storage:link` has been run
2. Verify that file permissions allow web server access to the storage directory
3. Ensure the database contains the correct paths
4. Use the `app:fix-image-paths` command to fix any legacy paths

## Fixing Legacy Paths

Run this command to fix any incorrectly formatted image paths:

```
php artisan app:fix-image-paths
```

This will find any images with paths starting with `/storage/` and move them to the correct location.
