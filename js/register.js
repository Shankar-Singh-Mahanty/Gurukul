$(document).ready(function () {
    $("#signupForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var formData = {
            username: $("#signupName").val(),
            email: $("#signupEmail").val(),
            password: $("#signupPassword").val(),
            contact: $("#signupPhone").val(),
            address: $("#signupAddress").val(),
        };

        $.ajax({
            type: "POST",
            url: "register.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    // Show success message
                    $("#signupMessage").html(
                        '<div class="alert alert-success">' +
                            response.message +
                            "</div>"
                    );

                    // Wait 2 seconds before redirecting
                    setTimeout(function () {
                        window.location.href = "index.html"; // Redirect after showing message
                    }, 2000);
                } else {
                    // Show error message
                    $("#signupMessage").html(
                        '<div class="alert alert-danger">' +
                            response.message +
                            "</div>"
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $("#signupMessage").html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            },
        });
    });

    // Reset form and messages when modal is closed
    $("#signupModal").on("hidden.bs.modal", function () {
        $("#signupForm")[0].reset();
        $("#signupMessage").html(""); // Clear messages
    });
});
