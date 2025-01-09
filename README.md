# ABZ REST Api
This project implements a simple REST API server.

###  Project deployment instructions:
```sh
git clone <repository URL>
```


```sh
cd abz-restapi
```

```sh
composer install
```

```sh
cp .env.example .env
```
 Open and configure the `.env` file

```sh
php artisan key:generate
```

```sh
php artisan migrate --seed
```

```sh
npm install
```

```sh
npm run dev
```

```sh
php artisan serve
```
For testing

```sh
php artisan test
```
###  It includes:
**Data Generation and Seeders**
 - A data generator and seeders have been implemented for initial database population with 45 users. 
The data is as close as possible to real user data.

**REST API Methods**
 - Various REST API methods have been implemented for the core functionalities of the system, including token generation, managing user data, and retrieving positions

**Image Processing with TinyPNG**
 - To store user images, they are processed through the TinyPNG service. The images are cropped to 70x70px and optimized for size before being saved.

**Frontend**
 - Displays a list of users with a "Show more" button, showing 6 users per page.
 - A form to add a new user, with no frontend validation, as all validation is handled on the server side.

### REST Api routes
**Endpoint: `GET /api/v1/token`**
Description:  This route generates an authentication token for the user. The token is needed to POST request to create a new user.
Response example: 
Status: `200`
```sh
{
    "success": true,
    "token": "etUOYJYybIa4RK5dLh2oLiRtRQ8Emkp2"
}
```

**Endpoint: `GET /api/v1/positions`**
Description:  This route returns a list of all positions.
Success response example: 
Status: `200`
```sh
{
    "success": true,
    "positions": [
        {
            "id": 1,
            "name": "Mechanical Engineer"
        },
        {
            "id": 2,
            "name": "Auditor"
        },
    ]
}
```

Error response example: 
Status: `404`
```sh
{
    "success": false,
    "message": "Positions not found"
}
```

**Endpoint: `POST /api/v1/users`**
Description:  Request to create a new user.

> `Note`: a token is required for each POST request !!!
to get it use `GET /api/v1/token`

Request Parameters:  
  - `name` (string, required): The name of the user.
  - `email` (string, required): The email address of the user. Must be unique.
  - `phone` (string, required): The phone number. Must start with +380 and 9 digits, must also be unique.
  - `position_id` (int, required): The id of the position. Must be an existing position in the database.
  - `photo` (file, required): The photo for the new user. Must be no larger than 5 MB, with a jpeg or jpg extension.

Success response example: 
Status: `201`
```sh
{
    "success": true,
    "message": "New user successfully registered",
    "user_id": 5
}
```
  
Response with token error example: 
Status: `401`
```sh
{
    "success": false,
    "message": "The token expired."
}
```

Response with phone or email validation error example: 
Status: `409`
```sh
{
    "success": false,
    "message": "User with this phone or email already exist"
}
```


Response body with other fields validation errors example: 
Status: `422`
```sh
{
    "success": false,
    "message": "Validation failed",
    "fails": {
        "phone": [
            "The phone format is invalid."
        ],
        "position_id": [
            "The position id must be an integer."
        ],
        "photo": [
            "The photo field is required."
        ]
    }
}
```

**Endpoint: `GET /api/v1/users`**
Description:  Request to get a list of users.
Optional request Parameters:  
  - `count` (string): Number of users on the page.
  - `page` (string): Номер сторінки.

Success response example: 
Status: `200`
```sh
{
    "success": true,
    "page": 1,
    "total_pages": 10,
    "total_users": 49,
    "count": 5,
    "links": {
        "next_url": "http://127.0.0.1:8000/api/v1/users?page=2",
        "prev_url": null
    },
    "users": [
        {
            "id": 49,
            "name": "Artem",
            "email": "newssssss21@gmail.com",
            "phone": "+380214545454",
            "position": "Airframe Mechanic",
            "position_id": 4,
            "registration_timestamp": 1736421851,
            "photo": "http://127.0.0.1:8000/images/users/8da79cf3-b667-4936-b409-7e0ae887ca29.jpg"
        },
        ...
    ]
}
```

**Endpoint: `GET /api/v1/users?count=3`**
Description:  Request to get a list of users with count of users per page.
Success response with count example: 
Status: `200`
```sh
{
    "success": true,
    "page": 1,
    "total_pages": 17,
    "total_users": 49,
    "count": 3,
    "links": {
        "next_url": "http://127.0.0.1:8000/api/v1/users?page=2",
        "prev_url": null
    },
    "users": [
        {
            "id": 49,
            "name": "Artem",
            "email": "newssssss21@gmail.com",
            "phone": "+380214545454",
            "position": "Airframe Mechanic",
            "position_id": 4,
            "registration_timestamp": 1736421851,
            "photo": "http://127.0.0.1:8000/images/users/8da79cf3-b667-4936-b409-7e0ae887ca29.jpg"
        },
        ...
    ]
}
```

Error response with wrong count type example: 
Status: `422`
```sh
{
    "success": false,
    "message": "Validation failed",
    "fails": {
        "count": [
            "The count must be an integer."
        ]
    }
}
```

**Endpoint: `GET /api/v1/users?page=2`**
Description:  Request to get a list of users with page of users.
Success response with page example: 
Status: `200`
```sh
{
    "success": true,
    "page": 2,
    "total_pages": 10,
    "total_users": 49,
    "count": 5,
    "links": {
        "next_url": "http://127.0.0.1:8000/api/v1/users?page=3",
        "prev_url": "http://127.0.0.1:8000/api/v1/users?page=1"
    },
    "users": [
        {
            "id": 44,
            "name": "Terrance Champlin",
            "email": "okuneva.kristopher@gmail.com",
            "phone": "+380290756946",
            "position": "Budget Analyst",
            "position_id": 3,
            "registration_timestamp": 1736414270,
            "photo": "http://127.0.0.1:8000/images/users/photo_4.jpg"
        },
        ...
    ]
}
```

Error response with wrong page type example: 
Status: `422`
```sh
{
    "success": false,
    "message": "Validation failed",
    "fails": {
        "page": [
            "The page must be an integer."
        ]
    }
}
```

**Endpoint: `GET /api/v1/users/{id}`**
Description:  Request to receive data of a specific user.
Success response example: 
Status: `200`
```sh
{
    "success": true,
    "user": {
        "id": 1,
        "name": "Osborne Vandervort DVM",
        "email": "amari43@gmail.com",
        "phone": "+380252844378",
        "position": "Mechanical Engineer",
        "position_id": 1,
        "photo": "http://127.0.0.1:8000/images/users/photo_4.jpg"
    }
}
```

Error response with wrong id type example: 
Status: `404`
```sh
{
    "success": false,
    "message": "The user with the requested id does not exist",
    "fails": {
        "userId": [
            "The user must be an integer."
        ]
    }
}
```

Error response with with a non-existent id example: 
Status: `404`
```sh
{
    "success": false,
    "message": "User not found"
}
```