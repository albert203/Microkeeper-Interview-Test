// Script to Create the Logout button after successful login
// After Clicking it will destroy the session and redirect to the login page

// const logoutBtn = document.querySelector('#logout-btn');
// const loginBtn = document.querySelector('.login-btn');
// const btnContainer = document.querySelector('#btn-container');

// loginBtn.addEventListener('click', (e) => {
//   console.log('Login Button Clicked');
//   e.preventDefault();
//   let isLoggedIn = true;
//   if (isLoggedIn) {
//     // Create the Logout Button
//     const logoutBtn = document.createElement('button');
//     logoutBtn.style =
//       'margin-top:10px;background-color: green;font-size: 18px;color: #fff;width: 100%;padding: 15px 20px;' +
//       'border: none;border-radius: 5px;font-weight: 700;cursor: pointer;';
//     logoutBtn.textContent = 'Logout';
//     logoutBtn.id = 'logout-btn';
//     btnContainer.append(logoutBtn);

//     logoutBtn.addEventListener('click', (e) => {
//       e.preventDefault();
//       document.createElement('p').innerHTML = '<?php session_destroy(); ?>';
//     });
//   } else {
//     console.log('You are not logged in');
//   }
// });
