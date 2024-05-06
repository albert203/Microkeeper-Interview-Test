<?php
        // db connection
        $host = 'localhost';
        $dbname = 'myfirstdatabase';
        $dbusername = 'root';
        $dbpasword = 'password';

        try {
            $dbo = new PDO('mysql:host=$host;dbname=$databasename', $dbusername, $dbpasword);
            $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        } catch (PDOException $e){
            echo 'Connection failed: ' . $e->getMessage();
        }

        // gets the form data
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            echo $email;
            echo $password;

            

            // Create User, Insert the data into the database

            $query = "INSERT INTO  users ($email, $password) VALUES (:email, :password)";
            $stmt = $dbo->prepare($query);

            // hash the password
            $hashedpassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt -> bindParam(':email', $email);
            $stmt -> bindParam(':password', $hashedpassword);
            $stmt -> execute();

            //ERROR HANDLING
            $errors = [];

            // I email is missing 
            if (is_input_empty($email, $password)) {
                $errors["empty_input"] = 'Email is required';
            }

            // If email is invalid
            if (is_email_valid($email)){
                $errors["invalid_email"] = 'Email is invalid';
            }

            if ($errors){
                echo $_SESSION['errors'] = $errors;
                header('Location: ../index.php');
                die();
            }


            // PasswordVerification($password, $hashedpassword);

        } else {
            header('Location: ../index.php');
            die();
        }

        // When Login button is clicked, verify the password
        function PasswordVerification($password, $hashedpassword){
            // verify hashed password is correct, compares to database
            $password = $_POST['password'];
            if (password_verify($password, $hashedpassword)) {
                echo 'Password is correct';
            } else {
                echo 'Password is incorrect';
            }
        }

        function is_input_empty($email, $password){
            if (empty($email) || empty($password)) {
                return true;
            } else {
                return false;
            }
        }

        function is_email_valid($email){
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            } else {
                return false;
            }
        }

   


?>