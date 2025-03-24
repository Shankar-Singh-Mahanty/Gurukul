$(document).ready(function () {
    $("#loginForm").submit(function (event) {
        event.preventDefault();

        var formData = {
            loginEmail: $("#loginEmail").val(),
            loginPassword: $("#loginPassword").val(),
        };

        // Hide error message when login button is clicked
        $("#loginError").fadeOut();

        $.ajax({
            type: "POST",
            url: "login.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    window.location.href = response.redirect;
                } else {
                    $("#loginError")
                        .text(response.message)
                        .addClass("loginError")
                        .fadeIn();
                    $("#loginEmail, #loginPassword").val(""); // Clear fields after error
                }
            },
            error: function () {
                $("#loginError")
                    .text("An error occurred. Please try again.")
                    .addClass("loginError")
                    .fadeIn();
                $("#loginEmail, #loginPassword").val(""); // Clear fields on error
            },
        });
    });

    // **Clear error message when user starts typing**
    $("#loginEmail, #loginPassword").on("input", function () {
        $("#loginError").fadeOut();
    });

    // **Reset error message and fields when login modal is closed**
    $("#loginModal").on("hidden.bs.modal", function () {
        $("#loginForm")[0].reset(); // Reset the form
        $("#loginError").text("").removeClass("loginError").hide(); // Clear and hide error message
    });
});
