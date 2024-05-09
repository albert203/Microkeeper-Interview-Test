<?php
   

    // // Logout process
    // if (isset($_GET['logout'])) {
    //     echo $_SESSION['user_id'];
    //     // Unset all session variables

    //     session_unset();

    //     // Destroy the session
    //     session_destroy();
    //     echo $_SESSION['user_id'];
        

    //     // Redirect to the index page
    //     header('Location: ' . $_SERVER['PHP_SELF']);
    //     echo 'Logged out';
    //     // test echo that the user is logged out
    //     echo '<br>';
    //     echo 'User id: ' . $_SESSION['user_id'];
    //     echo '<br>';
    //     exit();
    // }

    

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

    // Brute force attack prevention
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
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
            // header("Location: ./index.php");
            // die();
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
                    <p class="sub-text"> See what is going on with your business</p>
                </div>
                <form class="form" method="POST">
                    <ul class ="inputs-container" style="list-style-type: none; font-size:1em;">
                        <label for="email">Email</label>
                        <li class="border" id="email-border">
                            <input type="text" name="email" placeholder="mail@abc.com">
                        </li>
                        <label for="password">Password</label>
                        <li class="border">
                            <input id="password" type="password" name="password" placeholder="Password">
                        </li>
                    </ul>
                    
                    <div class="remember-me-container">
                        <div class="inner-checkbox-container">
                            <input class="remember-me" type="checkbox" name="remember" id="remember" checked>
                            <label for="remember-me-text">Remember Me</label>
                        </div>
                        <span class="span"><a href="#">Forgot password?</a></span>
                    </div>
                    
                    <button class="login-btn" type="submit">Login</button>

                    <!-- <div id="btn-container"></div> -->
                </form>

                <footer>
                    <p class="footer-text">Not Registered Yet?<a href="#">Create an Account</a></p>
                </footer>
                <?php
                    display_login_errors();
                ?>
                <script src="script.js"></script>
            </div>
        </aside>
    </div>

    <?php
        // db connection
        $host = 'localhost';
        $dbname = 'mydatabase';
        $dbusername = 'root';
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


        
        // LOGIN, getting values and checking against the database

        
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_SESSION['login_locked'])) {

            $login_successful = false;
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
                            $login_successful = true;
                            
                            
                            // CREATE A SESSION FOR THE USER
                            // retrieve the user id and email
                            $user_id = $user['id'];
                            $_SESSION['user_id'] = $user_id; 
                            $_SESSION['email'] = $email; 

                            echo "<br><p>user id: $user_id </p>";
                            // Redirect to the account page
                            header('Location: ./account.php');
                            exit();
                            
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
                echo $_SESSION['login_attempts'];
                // echo $errors;
            }
        }

            if (!$login_successful) {
                // Increment the login attempts if the login is not successful
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
                            echo "<br>To many incorrect login attempts. Account locked for " . $_SESSION['lockedOutTime'] . " seconds";
                            // exit();
                        } else{
                            echo 'Account login is now unlocked, please login';
                            // unlock the account, reset the login attempts, and time of last login attempt
                            unset($_SESSION['login_locked']);
                            unset ($_SESSION['last_login_attempt']);
                            $_SESSION['login_attempts'] = 0;
                        }
                    }
                    exit();
                }
            } else {
                // Login successful, reset attempts
                $_SESSION['login_attempts'] = 0;
            }
        } else {
            // echo 'Post request failed';
        }

        // if login is not successful, increment the login attempts
        



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

?>

</body>
</html>