<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div style="display: flex; justify-content: center; align-items: center;height: 100vh;">
        <section class="img-container" style="">
            <img src="illustration.png" alt="logo">
        </section>


        <aside class="aside-container">
            <h1 style="font-size: 2em;">Login to your Account</h1>
            <p> See what is going on with your business</p>
            <form action="post" style="">
                <ul style="list-style-type: none; font-size:1em;">
                    <li>
                        <label class="email" for="email">Email</label>
                        <br>
                        <input type="text" name="email" placeholder="Email">
                    </li>
                    <li>
                        <label for="password">Password</label>
                        <br>
                        <input type="password" name="password" placeholder="Password">
                    </li>
                </ul>
                
                <div>
                    <div>
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember me</label>
                    
                        <span class="span"><a href="#">Forgot password?</a></span>
                    </div>
                </div>
                
                <button type="submit">Login</button>
                <p>Not Registered Yet?<a href="#">Create an Account</a></p>
            </form>
            <br>
            <button>logout</button>
        </aside>

        
    </div>

    
    

</body>
</html>