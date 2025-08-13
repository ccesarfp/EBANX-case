# EBANX Case - API Challenge

This project is an implementation of the **EBANX Software Engineer Take-home Assignment**, which consists of creating a simple API for banking operations (deposit, withdraw, transfer, and balance inquiry), without the need for a database or persistence mechanism.

## 📋 Challenge Requirements

The API must expose two main endpoints:

- **GET /balance**  
  Returns the account balance for the provided `account_id` parameter.

- **POST /event**  
  Receives events of type `deposit`, `withdraw`, or `transfer` and performs the corresponding operations.

The challenge **does not require durability** (persistence), but this implementation stores the data in memory and also in a temporary file (`/tmp/accounts.json`) for simulation purposes.

An additional endpoint `POST /reset` was implemented to facilitate state cleanup between executions, as expected in the EBANX automated test suite.

## 🛠 Technologies Used

- **PHP 8.1+**
- **Slim Framework** (routing and HTTP layer)
- **php-di** (Dependency Injection)
- **Docker** + **Docker Compose**
- **Nginx** (web server)
- **PHPUnit** (unit testing)
- **ngrok** (public tunneling for API exposure)

## 📂 Project Structure

```
App/
├── Controllers/ # HTTP controllers
├── Services/ # Business logic
├── Repositories/ # Storage implementations
├── Enums/ # Constants and enums
├── Exceptions/ # Custom exceptions
└── Utils/ # Utility functions
routes/ # Route definitions
public/ # Application entry point
tests/ # Unit tests
.docker/ # Container configurations
```

## 🚀 Running the Project

### Prerequisites
- **Docker** and **Docker Compose** installed
- **ngrok** installed (download from: [https://ngrok.com/download](https://ngrok.com/download))

### Steps

1. Clone this repository:
```
git clone https://github.com/ccesarfp/EBANX-case.git
cd EBANX-case
```

2. Start the containers:
```
docker compose up
```

3. The application will be available at:
```
http://localhost:8989
```

4. Expose the API to the internet with ngrok:
```
ngrok http 8989
```

5. ngrok will provide a public URL such as:
```
https://1234-56-78-90.ngrok-free.app -> http://localhost:8989
```
Use this https://...ngrok-free.app URL when running the EBANX automated test suite.

### Endpoints

1. Reset Memory
```
POST /reset
```

2. Create Event
```
POST /event
Content-Type: application/json

{
  "type": "deposit",
  "destination": "100",
  "amount": 10
}
```
Accepted types:
- deposit (creates account if it does not exist)
- withdraw
- transfer

3. Check Balance
```
GET /balance?account_id=100
```

### Tests

To run the unit tests:
```
docker-compose exec app vendor/bin/phpunit
```
