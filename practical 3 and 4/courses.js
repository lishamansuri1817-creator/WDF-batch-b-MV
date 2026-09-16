function selectCourse(courseName) {

    var message = document.getElementById("courseMessage");

    message.textContent = "You have selected " + courseName + ".";

    alert("You selected " + courseName + " course.");
}