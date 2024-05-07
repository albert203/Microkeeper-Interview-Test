// fucntion that hides the password being typed
// replaces characters with . to hide password

// function hidePassword() {
//     // Get the password input element
//     const passwordInput= document.getdocumentbyId("password").value;
    
//     passwordInput.addEventListener("focus", function() {
//         passwordInput.value = passwordInput.dataset.originalValue || "";
//       });

//       passwordInput.addEventListener("blur", function() {
//         const originalValue = passwordInput.value;
//         passwordInput.value = originalValue.replace(/\S/g, ".");
//         passwordInput.dataset.originalValue = originalValue;
//       });


    // const hiddenPassword = textPassword.replace(/\S/g, "."); // Replace all non-whitespace characters with "."
// }