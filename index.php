<?php
    // Create the cookies configuration for security,
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);

    session_set_cookie_params([
        'lifetime' => 1800,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
    ]);

    session_start();
   
    // db connection
    $host = 'localhost';
    $dbname = 'mydatabase';
    $dbusername = 'root';
    // $dbpassword = '';
    $dbpassword = 'password';

    try {
        $dbo = new PDO("mysql:host=$host", $dbusername, $dbpassword);
        $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e){
        echo 'First Connection failed: ';
        echo 'Connection failed: ' . $e->getMessage();
    }

    // Create a database using php
    $queryDb = "CREATE DATABASE IF NOT exists mydatabase";

    // Prepare to prevent SQL injection
    $sqlDb = $dbo->prepare($queryDb);

    // Execute the query
    $sqlDb->execute();

    // Reconnect with the newly created mydatabase
    $dbo = new PDO("mysql:host=$host;dbname=mydatabase", $dbusername, $dbpassword);
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Create a table
    $queryTable = "CREATE TABLE IF NOT EXISTS users(
        id INT(6) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $sqlTable = $dbo->prepare($queryTable);

    $sqlTable->execute();

    // Create three users
    $queryUserOne= "INSERT INTO users (email, password) VALUES ('johndoe@gmail.com', 'john1234')";
    $queryUserTwo= "INSERT INTO users (email, password) VALUES ('testuser@gmail.com', 'test1234')";
    $queryUserThree= "INSERT INTO users (email, password) VALUES ('Janedoe@gmail.com', 'Jane1234')";
    
    $users = [$queryUserOne, $queryUserTwo, $queryUserThree];
    
    // hash the passwords
    function hashPassword($password, $user){
        $santisedPassword = htmlspecialchars($password);
        $hashedpassword = password_hash($santisedPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return $hashedpassword;
    }
    
    // loop through the users array and insert the data into the database
    // verify the user is not already in the database
    foreach ($users as $user) {
        // check if the email is already in the database
        $email = htmlspecialchars(explode("'", $user)[1]);
        $checkQuery = "SELECT * FROM users WHERE email = '$email'";
        $checkResult = $dbo->prepare($checkQuery);
        $checkResult->execute();

        
        // If the email is not in the database, hash the password and insert the user
        if ($checkResult->rowCount() == 0) {
          try {   
            // explode the password from the user (not in db)
            $password = explode("'", $user)[3];
            echo $password;
            echo '<br>';

            // Hash the password
            $hashedpassword = hashPassword($password, $user);
            echo $hashedpassword;

            // Bind the parameters
            $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
            $stmt = $dbo->prepare($sql);
            $stmt-> bindParam(':email', $email);
            $stmt-> bindParam(':password', $hashedpassword);
            $stmt -> execute();

            echo '<div>';
            echo '<p>' . $email . ' Created Successfully</p>';
            echo '</div>';
          } catch (PDOException $e) {
            echo "user execute failed";
            echo $e->getMessage();
          }
        } else {
            // echo '<div>';
            // echo '<p>' . $email . ' already exists!</p>';
            // echo '</div>';
        }
      }
    
    // LOGIN  
    $login_successful = false;
    // $_SESSION['login_locked'] = false;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_SESSION['login_locked'])) {

        // get values from the form
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);
        
        
        // query to search for the user in the db by email
        $checkQuery = "SELECT * FROM users WHERE email = :email";
        $result = $dbo->prepare($checkQuery);
        $result->bindParam(':email', $email);
        $result->execute();
    
        // check if the email exists
        if ($result->rowCount() == 1) {
            // get the user from the database
            $user = $result->fetch();      
            // echo 'Email exists: ' . $user['email'];
            echo '<br>';
    
            try {
                // query to select the user with the given email and password
                $QueryDbUser = "SELECT * FROM users WHERE email = :email AND password = :password";
                $stmt = $dbo->prepare($QueryDbUser);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $user['password']); // bind the hashed password from the database
                $stmt->execute();
    
                if ($stmt->rowCount() == 1) {
                    // verify the password
                    if (password_verify($password, $user['password'])) {
                        
                        echo 'Password is correct, Login Successful';
                        
                        
                        
                        // CREATE A SESSION FOR THE USER
                        // retrieve the user id and email
                        

                        // Redirect to the account page
                        $user_id = $user['id'];
                        echo "<br><p>user id: $user_id </p>";
                        $_SESSION['user_id'] = $user_id; 
                        $_SESSION['email'] = $email; 

                        $_SESSION['loggedIn'] = true;
                        
                        // header($_SERVER["PHP_SELF"]);
                        $login_successful = true;
                       
                        
                    } else {
                        $login_successful = false;
                        echo 'Password is incorrect';
                    }
                } else {
                    $login_successful = false;
                    echo 'Password is incorrect';
                }
            } catch (PDOException $e) {
                $login_successful = false;
                echo 'user account query failed' . $e->getMessage();
            }
        } else {
            $login_successful = false;
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            echo "login attempts: " . $_SESSION['login_attempts'];
        }
    }

        if (!$login_successful) {
            // Increment the login attempts if the login is not successful
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            $_SESSION['login_attempts']++;
          
            // Lock the account after 5 attempts
            if ($_SESSION['login_attempts'] >= 5) {
                // Lock the account for 20 seconds
                $lockoutInterval = 20; 
                if (!isset($_SESSION['last_login_attempt'])){
                    $_SESSION['last_login_attempt'] = time();
                }
                
                $_SESSION['login_locked'] = true;
                // If locked after 5 attepmpts, lock the account for 20 seconds
                if (isset($_SESSION['login_locked'])) {
                    // when time difference  hits 20 seconds, unlock the account
                    $timeDifference = time() - $_SESSION['last_login_attempt'];

                    // echo  $timeDifference;
                    // tells user feedback how long locked out for
                    $_SESSION['lockedOutTime'] =  $lockoutInterval - $timeDifference;

                    if ($timeDifference <= $lockoutInterval) {
                        echo "To many incorrect login attempts. Account locked for " . $_SESSION['lockedOutTime'] . " seconds";
                    } else{
                        echo 'Account login is now unlocked, please login';
                        // unlock the account, reset the login attempts, and time of last login attempt
                        unset($_SESSION['login_locked']);
                        unset ($_SESSION['last_login_attempt']);
                        $_SESSION['login_attempts'] = 0;
                    }
                } 
            }
        } else {
            // Login successful, reset attempts
            $_SESSION['login_attempts'] = 0;
        }
    } else {
        // echo 'Post request failed';
    }

    // ERROR HANDLING FUNCTIONS
    // check if the input fields are empty
    function is_input_empty($email, $password){
        if(empty($email) || empty($password)){
            return true;
        } else {
            return false;
        }
    }

    // check if the email is invalid
    function is_email_invalid($email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        } else {
            return false;
        }
    }

    // display the errors in the login form
    function display_login_errors() {
        if (isset($_SESSION["errors_login"])) {
            $errors = $_SESSION["errors_login"];
        
            foreach ($errors as $error) {
            echo "<p style='color:red; text-transform:uppercase;'>" . $error . "</p>";
            }
        
            unset($_SESSION["errors_login"]);
        }
    }


    if (!isset($_SESSION["last_regeneration"])){
        regenerate_session_id();

    } else{
        $interval = 60 * 30;
        if (time() - $_SESSION["last_regeneration"] >= $interval){
            regenerate_session_id();
        }
    }

    function regenerate_session_id(){
        session_regenerate_id(true);
        $_SESSION["last_regeneration"] = time();
    }

    //ERROR HANDLING
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){

            $email = $_POST['email'];
            $password = $_POST['password'];
            $errors = [];

        if (is_input_empty($email, $password)){
            $errors["empty_input"] = "Fill in all fields!";
        }
        if (is_email_invalid($email)){
            $errors["invalid_email"] = "Invalid email used!";
        }

        if ($errors) {
            $_SESSION["errors_login"] = $errors;
        }
    } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css"/>
    
    <style>
        * {
        padding: 0;
        box-sizing: border-box;
        font-family: 'Nunito Sans', sans-serif;
        margin: 0;
        }

        .outer-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        width: 100%;
        }

        section {
        height: 100vh;
        }

        .img-container img {
        object-fit: cover;
        max-width: 100%;
        height: auto;
        }

        .img-container {
        display: flex;
        }

        aside {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 70%;
        height: auto;
        }

        .inner-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        margin: auto;
        gap: 30px;
        width: 70%;
        height: 100%;
        padding: 50px;
        }

        .cross-container {
        display: flex;
        justify-content: flex-start;
        width: 100%;
        }

        .text-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 10px;
        width: 100%;
        }

        .sub-text {
        font-family: 'Nunito Sans', sans-serif;
        color: #525252;
        }

        .form {
        width: 90%;
        }

        .inputs-container {
        list-style-type: none;
        font-size: 1em;
        }

        .login-text {
        font-family: 'Nunito Sans', sans-serif;
        font-weight: 700;
        color: #525252;
        font-size: 1.6em;
        }

        .border {
        padding: 13px 10px;
        gap: 13px;
        border: 1px solid #ded2d9;
        border-radius: 5px;
        opacity: 1;
        }

        #email-border {
        margin-bottom: 15px;
        }

        .border:focus {
        border: 1px solid #7f265b;
        transition: 0.5s;
        }

        input::placeholder {
        color: #e0e0e0;
        }

        input {
        border: none;
        outline: none;
        }

        input:active {
        opacity: 1;
        transition: ease-in;
        }

        label[for='email'],
        label[for='password'] {
        font-family: 'Nunito Sans', sans-serif;
        color: #828282;
        font-size: 14px;
        }

        .remember-me {
        accent-color: #7f265b;
        }

        label[for='remember-me-text'] {
        font-size: 12px;
        color: #a1a1a1;
        }

        .remember-me-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 10px 0 20px;
        }

        .span a {
        color: #7f265b;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        }

        .login-btn {
        background-color: #7f265b;
        font-size: 18px;
        color: #fff;
        width: 100%;
        padding: 15px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 700;
        cursor: pointer;
        }

        .logout-btn {
        background-color: #7f265b;
        font-size: 18px;
        color: #fff;
        padding: 15px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 700;
        cursor: pointer;
        }

        .logout-btn a{
            text-decoration: none;
        }

        footer {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 100%;
        }

        .footer-text {
        font-size: 18px;
        color: #828282;
        }

        .footer-text a {
        color: #7f265b;
        text-decoration: none;
        font-weight: 600;
        margin-left: 10px;
        }

        @media (max-width: 1600px) {
        /* Styles for screens up to 1600px */
        }

        @media (max-width: 1200px) {
        /* Styles for screens up to 1200px */
        }

        @media (max-width: 992px) {
        .outer-container {
            flex-direction: column;
            height: auto;
        }

        .img-container {
            height: 50vh;
        }

        .img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        aside {
            width: 100%;
            padding: 20px;
        }

        .inner-container {
            width: 100%;
            padding: 20px;
        }
        }

        @media (max-width: 768px) {
        .img-container {
            height: 30vh;
        }

        .img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .inner-container {
            gap: 20px;
        }

        .login-text {
            font-size: 1.2em;
        }

        .form {
            width: 100%;
        }

        .border {
            padding: 10px;
        }

        .remember-me-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .login-btn {
            font-size: 16px;
            padding: 12px 16px;
        }

        .footer-text {
            font-size: 16px;
        }
        }
    </style>
</head>
<body>

    <div class="outer-container">
    <section class="img-container">
        <img src="./img/illustration.png" alt="logo">
    </section>

    <aside class="aside-container">
        <div class="inner-container">

        <div class="cross-container">
            <img src="img/cross.png" alt="logo">
        </div>

        <div class="text-container">
            <h1 class="login-text">Login to your Account</h1>
            <p class="sub-text">See what is going on with your business</p>
        </div>

        <?php
                if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true) {
                    // User is logged in, show the logout button
                    echo '<p>Welcome, ' . htmlspecialchars($_SESSION['email']) . '</p>';
                    echo '<form method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                    echo '<button class="logout-btn" type="submit"><a>Logout</a></button>';
                    echo '</form>';
                    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                        unset($_SESSION['loggedIn']);
                        unset($_SESSION['user_id']);
                        unset($_SESSION['email']);
                        session_destroy();
                        
                        
                        // echo 'You have been logged out';
                    }
                } else {
                    // User is not logged in, show the login form
                    echo '<form class="form" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                    echo '<ul class="inputs-container">';
                    echo '<label for="email">Email</label>';
                    echo '<li class="border" id="email-border">';
                    echo '<input type="text" name="email" placeholder="mail@abc.com">';
                    echo '</li>';
                    echo '<label for="password">Password</label>';
                    echo '<li class="border">';
                    echo '<input id="password" type="password" name="password" placeholder="Password">';
                    echo '</li>';
                    echo '</ul>';
                    echo '<div class="remember-me-container">';
                    echo '<div class="inner-checkbox-container">';
                    echo '<input class="remember-me" type="checkbox" name="remember" id="remember" checked>';
                    echo '<label for="remember-me-text">Remember Me</label>';
                    echo '</div>';
                    echo '<span class="span"><a href="#">Forgot password?</a></span>';
                    echo '</div>';
                    echo '<button class="login-btn" type="submit">Login</button>';
                    echo '</form>';
                }
        ?>

        <footer>
            <p class="footer-text">Not Registered Yet?<a href="#">Create an Account</a></p>
        </footer>
        <?php display_login_errors(); ?>
        </div>
    </aside>
    </div>

</body>
</html>