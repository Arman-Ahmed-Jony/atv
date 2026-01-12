# Video API - Postman Usage Guide

## Setup

1. **Base URL**: Replace `your-app-url.test` with your actual Herd domain or server URL
2. **Create Environment Variables**:
   - `base_url` = `http://your-app-url.test`
   - `auth_token` = (will be set after login)

## API Endpoints

### 1. Register User

**Request:**
- Method: `POST`
- URL: `{{base_url}}/api/register`
- Headers:
  - `Content-Type: application/json`
  - `Accept: application/json`
- Body (raw JSON):
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
- Status: `201 Created`
- Body contains `user` object and `token`

---

### 2. Login

**Request:**
- Method: `POST`
- URL: `{{base_url}}/api/login`
- Headers:
  - `Content-Type: application/json`
  - `Accept: application/json`
- Body (raw JSON):
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
- Status: `200 OK`
- Body contains `user` object and `token`
- **Action**: Copy the `token` and save it to environment variable `auth_token`

---

### 3. Get Authenticated User

**Request:**
- Method: `GET`
- URL: `{{base_url}}/api/user`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - `Accept: application/json`

**Response:**
- Status: `200 OK`
- Body contains user details

---

### 4. Logout

**Request:**
- Method: `POST`
- URL: `{{base_url}}/api/logout`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - `Accept: application/json`

**Response:**
- Status: `200 OK`
- Message: "Logged out successfully"

---

## Video Endpoints

### 5. Upload Video

**Request:**
- Method: `POST`
- URL: `{{base_url}}/api/videos`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - Remove `Content-Type` header (Postman sets it automatically for multipart)
- Body:
  - Type: `form-data`
  - Fields:
    - `video` (File) - Select your video file (MP4 or WebM, max 100MB)
    - `title` (Text) - Video title (required)
    - `description` (Text) - Video description (optional)

**Response:**
- Status: `201 Created`
- Body contains:
  - `message`: "Video uploaded successfully"
  - `video`: Video object with all details including:
    - `file_url`: Direct URL to video file
    - `stream_url`: API endpoint for streaming
    - `download_url`: API endpoint for downloading

**Example Response:**
```json
{
  "message": "Video uploaded successfully",
  "video": {
    "id": 1,
    "title": "My Video",
    "description": "Video description",
    "file_path": "videos/1768130702_6963888ee6eaf.mp4",
    "file_url": "http://your-app-url.test/storage/videos/1768130702_6963888ee6eaf.mp4",
    "stream_url": "http://your-app-url.test/api/videos/1/stream",
    "download_url": "http://your-app-url.test/api/videos/1/download",
    "file_size": 4194304,
    "file_size_human": "4.00 MB",
    "duration": null,
    "mime_type": "video/mp4",
    "thumbnail_path": null,
    "thumbnail_url": null,
    "user_id": 1,
    "created_at": "2026-01-11T10:45:02+00:00",
    "updated_at": "2026-01-11T10:45:02+00:00"
  }
}
```

---

### 6. List All Videos

**Request:**
- Method: `GET`
- URL: `{{base_url}}/api/videos`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - `Accept: application/json`
- Query Parameters (optional):
  - `page` - Page number (default: 1)

**Response:**
- Status: `200 OK`
- Body contains paginated list of videos

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "My Video",
      "file_url": "http://your-app-url.test/storage/videos/...",
      "stream_url": "http://your-app-url.test/api/videos/1/stream",
      "download_url": "http://your-app-url.test/api/videos/1/download",
      ...
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 7. Get Single Video

**Request:**
- Method: `GET`
- URL: `{{base_url}}/api/videos/{video_id}`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - `Accept: application/json`

**Response:**
- Status: `200 OK`
- Body contains single video object

---

### 8. Stream Video (with Range Request Support)

**Request:**
- Method: `GET`
- URL: `{{base_url}}/api/videos/{video_id}/stream`
- Headers:
  - `Authorization: Bearer {{auth_token}}`
  - `Range: bytes=0-` (optional - for range requests, supports video seeking)

**Response:**
- Status: `206 Partial Content` (with Range header) or `200 OK`
- Content-Type: Video MIME type (e.g., `video/mp4`)
- Body: Video file stream

**Notes:**
- Supports HTTP Range requests for video seeking
- Perfect for Android ExoPlayer or MediaPlayer
- Use `stream_url` from video object for direct streaming

---

### 9. Download Video

**Request:**
- Method: `GET`
- URL: `{{base_url}}/api/videos/{video_id}/download`
- Headers:
  - `Authorization: Bearer {{auth_token}}`

**Response:**
- Status: `200 OK`
- Content-Type: Video MIME type
- Content-Disposition: `attachment; filename="video.mp4"`
- Body: Video file download

**Postman Usage:**
- Check "Send and Download" button
- File will be saved to your downloads folder

---

## Android Integration Notes

### For Streaming:
- Use `stream_url` from the video object
- Supports HTTP Range requests (206 Partial Content)
- Works with ExoPlayer, MediaPlayer, or VideoView
- Example: `http://your-app-url.test/api/videos/1/stream`

### For Downloading:
- Use `download_url` from the video object
- Download and save locally for offline playback
- Example: `http://your-app-url.test/api/videos/1/download`

### Authentication:
- Include `Authorization: Bearer {token}` header in all requests
- Token obtained from login endpoint
- Token expires when user logs out

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Login again and update `auth_token`

### 403 Forbidden
```json
{
  "message": "Unauthorized"
}
```
**Solution**: You don't have permission to access this video

### 404 Not Found
```json
{
  "message": "Video file not found"
}
```
**Solution**: Video file was deleted or doesn't exist

### 413 Payload Too Large
```json
{
  "message": "The uploaded file is too large. Maximum file size allowed is 100MB.",
  "error": "File size exceeds the maximum allowed limit."
}
```
**Solution**: Reduce video file size

---

## Postman Collection Setup

1. Create a new Collection: "Video Control API"
2. Create Environment: "Development"
   - Add variable: `base_url` = `http://your-app-url.test`
   - Add variable: `auth_token` = (leave empty, will be set after login)
3. For authenticated requests, use: `Authorization: Bearer {{auth_token}}`
4. Save login response token to environment variable using Postman's "Tests" tab:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.token);
}
```

---

## Testing Workflow

1. **Register** a new user (or use existing credentials)
2. **Login** to get authentication token
3. **Upload** a video file
4. **List** all videos to see your uploads
5. **Get** single video details
6. **Stream** video (test in browser or video player)
7. **Download** video for offline storage

---

**End of Guide**
