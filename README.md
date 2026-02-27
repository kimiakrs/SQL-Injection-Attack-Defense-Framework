# Secure vs Non-Secure Student Portal

This project was developed as part of a Software Engineering course to analyze and demonstrate the impact of SQL Injection (SQLi) vulnerabilities on web applications.

We implemented two separate versions of a Student Portal system:

* A Non-Secure version intentionally designed with common vulnerabilities.
* A Secure version implementing defensive security mechanisms and best practices.

The objective was to simulate real-world SQL injection attacks on both versions, evaluate system behavior, and demonstrate how secure coding practices prevent exploitation.

All source code is organized into dedicated directories:

[Non_Secure_Portal](./NonSecure-Sportal/)
[Secure_Portal](./Secure-Sportal/)

---

## Table of Contents

- [Project Overview](#project-overview)
- [Repository Structure](#repository-structure)
- [Technologies Used](#technologies-used)
- [How to Run the Project](#how-to-run-the-project)
- [SQL Injection Test Cases](#SQL-injection-test-cases)
- [Security Comparison](#security-comparison)
- [Disclaimer](#disclaimer)
- [License](#license)

---

## Project  Overview

The project focuses on:

* Building a student portal using PHP, Bootstrap and MariaDB.

* Designing database schema and authentication system.

* Implementing secure coding practices.

* Executing 10 structured SQL Injection test scenarios.

* Comparing system behavior between vulnerable and secured implementations.

* The goal was to understand:

* How SQL Injection works

* How attackers exploit poor input handling

* How prepared statements and validation prevent attacks

* Why layered security is essential in web applications

---

## Repository Structure

All codes are published in separate directories:

├── Secure-Sportal//
│   ├── config/ 
|   |  |     |── config.php
│   ├── db.php
│   ├── dataentry.php
│   └── resetpassword.php
│
├── NonSecure-Sportal/
│   ├── index.php
│   ├── home.php
│   ├── view_student.php
│   └── ...

### Secure Directory

Implements:

* PDO Prepared Statements

* Pasword hashing (password_hash, password_verify)

* Secure session management

* CSRF token protection

* XSS output escaping

* Input validation

* Rate limiting

* Stored procedures

* Database transactions

* Error logging with generic user messages

### Non-Secure Directory

* Intentionally contains:

* Raw SQL queries

* Plaintext password comparison

* No input validation

* No CSRF protection

* No secure session flags

* No output escaping

This version is used strictly for demonstrating vulnerabilities.

---

## Technologies Used


```
| Layer    | Technology              |
| -------- | ----------------------- |
| Backend  | PHP                     |
| Database | MariaDB     |
| Server   | Apache                  |
| Frontend | HTML, CSS, Bootstrap    |
| Testing  | Postman, Manual Testing |
```


### How to Run the Project

1. Requirements 
* PHP 7.4+

* Apache (XAMPP / LAMP / MAMP)

* MariaDB or MySQL

* phpMyAdmin (optional)

2. Clone Repository 

```
git clone https://github.com/kimiakrs/SQL-Injection-Attack-Defense-Framework.git
cd your-repository-folder
```

3. Prepare the virtual base like web hosting to implement your website 

4. Create Databse based on determined database name in the source code 

5. Configure database  web hosting and config files

6. Run Appliction

---

## SQL Injection Test Cases

```Non-Secure Test Scenarios
| Test ID     | Attack Type                |
| ----------- | -------------------------- |
| TC_SQLI_001 | SQL Injection Based on 1=1 is Always True          |
| TC_SQLI_002 | SQL Injection Based on ""="" is Always True     |
| TC_SQLI_003 | SQL Injection Based on Batched SQL Statements            |

```

```Secure Test Scenarios
| Test ID     | Attack Type                |
| ----------- | -------------------------- |
| TC_SQLI_001 | Comment Injection          |
| TC_SQLI_002 | Union-Based Injection      |
| TC_SQLI_003 | Blind Injection            |
| TC_SQLI_004 | Error-Based Injection      |
| TC_SQLI_005 | Boolean-Based Blind        |
| TC_SQLI_006 | HTTP Header Injection      |
| TC_SQLI_007 | Second-Order Injection     |
| TC_SQLI_008 | URL Parameter Injection    |
| TC_SQLI_009 | Stored Procedure Injection |
| TC_SQLI_010 | Time-Based Blind Injection |
```


---

## Security Comparison 

### Non-Secure Version

* Authentication bypass possible

* SQL errors exposed

* Sensitive data leakage

* Table deletion possible via batched queries

* Time delay confirms injection execution

### Secure Version

* Authentication bypass prevented

* No SQL errors exposed

* Prepared statements block injection

* URL manipulation ineffective

* Stored procedures protected

* Stable response time

* No unauthorized data exposure

---
---

## Disclaimer

DES is considered cryptographically weak and should not be used in modern production systems and this implementation is strictly for educational and academic purposes.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.