document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password_confirmation");
    const toggleIcon = document.getElementById("toggleConfirmPassword");

    toggleIcon.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";

      passwordInput.type = isPassword ? "text" : "password";
      // Đổi icon tương ứng
      toggleIcon.src = isPassword
        ? "./access/icon/eyeopen.svg" 
        : "./access/icon/eyeclose.svg"; 
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("togglePassword");

    toggleIcon.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";

      passwordInput.type = isPassword ? "text" : "password";
      // Đổi icon tương ứng
      toggleIcon.src = isPassword
        ? "./access/icon/eyeopen.svg" 
        : "./access/icon/eyeclose.svg"; 
    });
  });