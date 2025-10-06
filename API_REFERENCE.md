# API Reference - CCTV Dashboard

## 🔗 Base URL

```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

## 🔐 Authentication

### Static Token Authentication

For system-to-system communication.

**Header:**

```
Authorization: Bearer your-static-token
```

### Sanctum Token Authentication

For user-based API access.

**Header:**

```
Authorization: Bearer user-generated-token
```

---

## 📋 API Endpoints

### Static Token Endpoints

#### Get API Information

```http
GET /api/static/info
```

**Response:**

```json
{
  "success": true,
  "message": "API Information",
  "data": {
    "api_version": "1.0",
    "app_name": "CCTV Dashboard",
    "endpoints": {
      "test": {
        "main": "GET /api/static/test",
        "ping": "GET /api/static/test/ping",
        "echo": "POST /api/static/test/echo",
        "show": "GET /api/static/test/{id}",
        "create": "POST /api/static/test"
      }
    },
    "authentication": "Bearer Token (Static)",
    "header_required": "Authorization: Bearer your-static-token"
  }
}
```

#### Validate Static Token

```http
GET /api/static/validate
Authorization: Bearer your-static-token
```

**Response:**

```json
{
  "success": true,
  "message": "Token is valid",
  "data": {
    "valid": true,
    "timestamp": "2024-01-01 12:00:00"
  }
}
```

#### Test Endpoints

```http
GET /api/static/test
Authorization: Bearer your-static-token
```

**Response:**

```json
{
  "success": true,
  "message": "Static token authentication berhasil!",
  "data": {
    "authenticated": true,
    "timestamp": "2024-01-01 12:00:00",
    "server_time": "2024-01-01 12:00:00"
  }
}
```

---

### Authentication Endpoints

#### Register User

```http
POST /api/register
Content-Type: application/json
```

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    },
    "token": "1|abcdef123456..."
  }
}
```

#### Login User

```http
POST /api/login
Content-Type: application/json
```

**Request Body:**

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    },
    "token": "2|xyz789..."
  }
}
```

#### Logout User

```http
POST /api/logout
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Get Current User

```http
GET /api/me
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

---

### User Management Endpoints

#### List Users

```http
GET /api/users
Authorization: Bearer user-token
```

**Query Parameters:**

- `search` (optional): Search by name or email
- `per_page` (optional): Number of items per page (10, 20, 50, 100) - default: 10

**Response:**

```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 10,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 10
  }
}
```

#### Create User

```http
POST /api/users
Authorization: Bearer user-token
Content-Type: application/json
```

**Request Body:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "user"
}
```

**Response:**

```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "user",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

#### Get User

```http
GET /api/users/{id}
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

#### Update User

```http
PUT /api/users/{id}
Authorization: Bearer user-token
Content-Type: application/json
```

**Request Body:**

```json
{
  "name": "John Smith",
  "email": "johnsmith@example.com",
  "role": "admin"
}
```

**Response:**

```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "role": "admin",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

#### Delete User

```http
DELETE /api/users/{id}
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

#### Get Pagination Options

```http
GET /api/users/pagination/options
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "Pagination options retrieved successfully",
  "data": {
    "10": "10 per page",
    "20": "20 per page",
    "50": "50 per page",
    "100": "100 per page"
  }
}
```

---

## 📊 Response Formats

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    // Array of items
  ],
  "pagination": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "last_page": 10,
    "from": 1,
    "to": 10
  }
}
```

---

## 🔒 HTTP Status Codes

| Code | Description                              |
| ---- | ---------------------------------------- |
| 200  | OK - Request successful                  |
| 201  | Created - Resource created successfully  |
| 400  | Bad Request - Invalid request data       |
| 401  | Unauthorized - Authentication required   |
| 403  | Forbidden - Access denied                |
| 404  | Not Found - Resource not found           |
| 422  | Unprocessable Entity - Validation failed |
| 500  | Internal Server Error - Server error     |

---

## 🧪 Testing Examples

### cURL Examples

#### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login User

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Get Users (with token)

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer your-token-here"
```

#### Get Users with Pagination

```bash
curl -X GET "http://localhost:8000/api/users?per_page=20&search=john" \
  -H "Authorization: Bearer your-token-here"
```

#### Get Pagination Options

```bash
curl -X GET http://localhost:8000/api/users/pagination/options \
  -H "Authorization: Bearer your-token-here"
```

#### Create User (with token)

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "user"
  }'
```

#### Test Static Token

```bash
curl -X GET http://localhost:8000/api/static/test \
  -H "Authorization: Bearer your-static-token"
```

### JavaScript Examples

#### Using Fetch API

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch("/api/login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();
  return data;
};

// Get Users
const getUsers = async (token) => {
  const response = await fetch("/api/users", {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  const data = await response.json();
  return data;
};

// Create User
const createUser = async (token, userData) => {
  const response = await fetch("/api/users", {
    method: "POST",
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify(userData),
  });

  const data = await response.json();
  return data;
};
```

#### Using Axios

```javascript
// Setup axios instance
const api = axios.create({
  baseURL: "/api",
  headers: {
    "Content-Type": "application/json",
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// API calls
const authAPI = {
  login: (credentials) => api.post("/login", credentials),
  register: (userData) => api.post("/register", userData),
  logout: () => api.post("/logout"),
  me: () => api.get("/me"),
};

const userAPI = {
  list: (params) => api.get("/users", { params }),
  create: (userData) => api.post("/users", userData),
  get: (id) => api.get(`/users/${id}`),
  update: (id, userData) => api.put(`/users/${id}`, userData),
  delete: (id) => api.delete(`/users/${id}`),
};
```

---

## 🔧 Error Handling

### Common Error Scenarios

#### Authentication Errors

```json
{
  "success": false,
  "message": "Unauthorized: Invalid or missing token"
}
```

#### Validation Errors

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

#### Not Found Errors

```json
{
  "success": false,
  "message": "User not found"
}
```

#### Server Errors

```json
{
  "success": false,
  "message": "Internal server error"
}
```

### Error Handling Best Practices

1. **Always check the `success` field** in responses
2. **Handle validation errors** by displaying field-specific messages
3. **Implement retry logic** for network errors
4. **Show user-friendly messages** for common errors
5. **Log errors** for debugging purposes

---

## 📈 Rate Limiting

The API implements rate limiting to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **User management endpoints**: 60 requests per minute
- **Static token endpoints**: 100 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

## 🔄 Webhooks (Future Feature)

Webhooks will be available for real-time notifications:

- User created
- User updated
- User deleted
- Authentication events

---

## 📝 Changelog

### Version 1.0.0

- Initial API release
- User management endpoints
- Authentication with Sanctum
- Static token authentication
- Pagination support
- Comprehensive error handling

---

For more information, please refer to the main [Documentation](./DOCUMENTATION.md).
