<?php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  // Redirect if already logged in
  if (isset($_SESSION['user'])) {
      header('Location: index.php');
      exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
  
<!-- head  -->
<?php include ('utils/head.php') ?>
<!-- head  -->

<body class="bg-light d-flex align-items-center justify-content-center vh-100 font-custom">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="border rounded-3 p-4 bg-white">
          
          <center>
            <img src="./assets/etec.png" alt="" width="90px" height="90px" class="mb-2 rounded">
          </center>  

          <!-- Header -->
          <h3 class="mb-1 text-center">Welcome Back</h3>
          <p class="text-muted text-center small mb-4">Login to continue</p>

          <!-- Login Form -->
          <form id="loginForm">
            <!-- Email -->

            <!-- Error message placeholder -->
            <div id="loginMessage" class="text-center mb-2 small"></div>
            <div class="mb-3">
              <!-- <label for="email" class="form-label">Email address</label> -->
              <input type="email" class="form-control shadow-none border" id="email" name="email" placeholder="example@mail.com" required>
            </div>

            <!-- Password -->
            <div class="mb-3 position-relative">
                <!-- <label for="password" class="form-label">Password</label> -->
                <div class="input-group border rounded">
                    <input type="password" class="form-control shadow-none border-0" id="password" name="password" placeholder="Enter password" required>
                    <button class="btn border-0" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            

            <!-- Submit -->
            <div class="d-grid">
              <button type="submit" id="btnLogin" class="bg-etec-color border py-2 text-light rounded">Login</button>
            </div>
          </form>


          <!-- Footer -->
          <p class="text-center mt-3 mb-0 small">
            Don’t have an account? <a href="register.php" class="text-decoration-none">Register</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap + jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function(){

      // toggle password eye
      $("#togglePassword").on("click", function () {
        let password = $("#password");
        let type = password.attr("type") === "password" ? "text" : "password";
        password.attr("type", type);
        $(this).find("i").toggleClass("bi-eye bi-eye-slash");
      });

      // login submit
      $("#loginForm").on("submit", function(e){
          e.preventDefault();

          let btn = $("#btnLogin");
          let msg = $("#loginMessage");

          btn.prop("disabled", true).text("Loading...");
          msg.html(""); // clear old message

          let formData

          $.ajax({
            url: "api.php?endpoint=login",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(res){
              if(res.status){
    
                window.location.href = "index.php";

              } else {
                msg.removeClass("text-success").addClass("text-danger").html(res.message);
              }
            },
            complete: function(){
              btn.prop("disabled", false).text("Login");
            }
          });
      });


    });
  </script>
</body>
</html>
