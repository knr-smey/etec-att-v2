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
          <h3 class="mb-1 text-center">Create Account</h3>
          <p class="text-muted text-center small mb-4">Fill in your details to register</p>

          <!-- Register Form -->
          <form>
            <!-- Name -->        
            <div class="mb-3">
              <!-- <label for="name" class="form-label">Full Name</label> -->
              <input type="text" class="form-control shadow-none border" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
              <!-- <label for="email" class="form-label">Email address</label> -->
              <!-- error message -->
              <div id="errorMessage" class="text-danger mb-2 mt-1" style="display: none;"></div> 
              <!-- error message -->
              <input type="email" class="form-control shadow-none border" id="email" name="email" placeholder="example@mail.com" required>
            </div>

            <!-- Gender -->
            <div class="mb-3">
              <!-- <label for="gender" class="form-label">Gender</label> -->
              <select class="form-select shadow-none border" id="gender" name="gender" required>
                <option value="" disabled selected>Select your gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
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
              <button type="submit" class="bg-etec-color border py-2 text-light rounded" id="registerBtn">
                <span class="btn-text">Register</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>
          </form>

          <!-- Footer -->
          <p class="text-center mt-3 mb-0 small">
            Already have an account? <a href="login.php" class="text-decoration-none">Login</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-etec-color text-white">
          <h5 class="modal-title" id="successModalLabel">Registration Submitted</h5>
          <!-- <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <div class="modal-body text-center pb-5">
          <i class="bi bi-check-circle-fill fs-1 text-etec-color mb-3"></i>
          <p class="mb-0">Your registration has been received.<br>Please wait for admin approval.</p>
        </div>
        <!-- <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-light border shadow-none" data-bs-dismiss="modal">OK</button>
        </div> -->
      </div>
    </div>
  </div>

  <!-- jQuery & Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      // Get email from localStorage if exists
      let userEmail = localStorage.getItem('userEmail') || "";

      // Show modal automatically if email exists in localStorage
      if (userEmail) {
          const successModal = new bootstrap.Modal(document.getElementById('successModal'));
          successModal.show();

          // // Start checking approval
          setInterval(checkApproval, 3000);
      }

      // Toggle password visibility
      $("#togglePassword").on("click", function () {
        let password = $("#password");
        let type = password.attr("type") === "password" ? "text" : "password";
        password.attr("type", type);
        $(this).find("i").toggleClass("bi-eye bi-eye-slash");
      });

      // Handle form submit with AJAX
      $("form").submit(function(e){
        e.preventDefault();

        let btn = $("#registerBtn");
        let spinner = btn.find(".spinner-border");
        let btnText = btn.find(".btn-text");

        // Disable button and show spinner
        btn.prop("disabled", true);
        spinner.removeClass("d-none");
        btnText.text("Registering...");

        // Hide previous error
        $("#errorMessage").hide().text("");

        // Collect form data
        let formData = {
          name: $("#name").val(),
          email: $("#email").val(),
          gender: $("#gender").val(),
          password: $("#password").val()
        };
        // Save email to localStorage
        userEmail = formData.email;
        localStorage.setItem('userEmail', userEmail);
        // Call your API
        $.ajax({
            url: "api.php?endpoint=register",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response){
                if(response.status){
                  $("form")[0].reset();

                  // Show the success modal
                  const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                  successModal.show();

                  setInterval(checkApproval, 3000);
                } else {
                  $("#errorMessage").show().text(response.message);
                  $("#email").addClass('border-danger'); 
                }
            },
            error: function(xhr, status, error){
                $("#errorMessage").show().text("Something went wrong! Please try again.");
                console.error(xhr.responseText);
            },
            complete: function(){
                // Re-enable button and hide spinner
                btn.prop("disabled", false);
                spinner.addClass("d-none");
                btnText.text("Register");
            }
        });
      });
      
      function checkApproval() {
          if (!userEmail) return; // email not set yet
          $.ajax({
              url: 'api.php?endpoint=checkApproval&email=' + encodeURIComponent(userEmail),
              type: 'GET',
              dataType: 'json',
              success: function(res) {
                  // console.log(res);
                  if (res.status && res.data.approval === 'approved') {
                      localStorage.removeItem('userEmail');
                      window.location.href = 'index.php'; // redirect to dashboard
                  }
              }
          });
      }
      checkApproval();
      // setInterval(checkApproval, 3000);
    });
  </script>


</body>
</html>
