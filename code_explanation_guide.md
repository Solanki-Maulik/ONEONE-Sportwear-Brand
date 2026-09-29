# BCA 5th Sem Viva: Code Explanation Guide

In Saurashtra University BCA 5th Sem vivas, the external examiner will almost certainly ask you to open your code and explain specific lines, or ask "Where is the code for X?"

This guide breaks down the 4 most important pieces of code in your project. **Don't memorize the code—memorize what the functions do.**

---

## 1. The Database Connection (`db.php`)
**Examiner Question:** "Open the file where you connect to the database and explain it."

```php
<?php
// 1. We create a new connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "oneone");

// 2. We check if the connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```
**How to explain it:**
* "I am using the `mysqli` class in PHP to connect to the database."
* "The four parameters are: `localhost` (server name), `root` (default XAMPP username), blank `""` (no password for local XAMPP), and `oneone` (my database name)."
* "The `if` condition checks if there is a connection error, and if there is, it stops the page using the `die()` function."

---

## 2. The Login Code (`login.php`)
**Examiner Question:** "Show me the code where the user logs in. How do you check if the password is correct?"

```php
// 1. Get data from the form
$email = trim($_POST['email']);
$password = $_POST['password'];

// 2. Prepare the SQL query
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// 3. Check if user exists and verify password
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // 4. Start session and redirect
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
    }
}
```
**How to explain it:**
* "First, I get the email and password from the form using `$_POST`."
* "Then, I use a **Prepared Statement** (`$conn->prepare`). I use the `?` placeholder and `bind_param()` to prevent **SQL Injection** attacks. This is much safer than putting the variable directly into the SQL string."
* "The `bind_param("s", $email)` means I am binding a String (`s`) to the `?`."
* "Instead of matching passwords directly in SQL, I fetch the user's data and use PHP's `password_verify()` function. This compares the plain text password they entered with the hashed/encrypted password in the database."
* "If it matches, I store their ID in a `$_SESSION` variable so they stay logged in, and use `header("Location: index.php")` to redirect them to the home page."

---

## 3. The Registration Code (`login.php`)
**Examiner Question:** "How are you saving a new user to the database?"

```php
// 1. Encrypt the password before saving
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 2. Insert into database
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);
$stmt->execute();
```
**How to explain it:**
* "Before saving the user, I encrypt their password using `password_hash()` with `PASSWORD_DEFAULT`. This ensures that even if the database is hacked, the passwords are safe."
* "Then I write an `INSERT INTO` SQL query."
* "Again, I use prepared statements with three `?` marks. `bind_param("sss", ...)` binds three strings to those question marks: the username, email, and the hashed password."

---

## 4. Fetching Products for the Shop (`index.php`)
**Examiner Question:** "How are you displaying the products from the database onto the website?"

```php
// 1. Run the SQL Query
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 8");

// 2. Loop through the results in the HTML
<?php while ($item = $result->fetch_assoc()): ?>
    <div class="card">
        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
    </div>
<?php endwhile; ?>
```
**How to explain it:**
* "At the top of the page, I run a `SELECT` query. I am using a `JOIN` to combine the `products` table with the `categories` table, so I can get the actual category name instead of just the category ID."
* "Then, down in the HTML, I use a `while` loop with `$result->fetch_assoc()`."
* "This loop fetches one product row at a time as an associative array (where the column names are the keys). It loops and creates a new HTML `<div class="card">` for every single product in the database."
* "I use `htmlspecialchars()` when printing the name to prevent XSS (Cross-Site Scripting) attacks, making the site more secure."

---

## Quick Checklist for BCA 5th Sem Vivas:
- [ ] **Start XAMPP:** Make sure Apache and MySQL are running before they call you.
- [ ] **Know your database name:** Your database name is `oneone`.
- [ ] **Know your table names:** `users`, `products`, `categories`, `cart_items`, `orders`.
- [ ] **Be ready to make a small change:** Examiners love to say "Change this background color to red" or "Change the text on this button." Know that your CSS is in `style.css` and `login.css`.
- [ ] **Sessions:** Know that `require 'includes/session.php';` is at the top of pages to track if the user is logged in.
