function editProfile() {

    var name = prompt("Enter your name:");

    if (name === null) {
        return;
    }

    if (name.trim() === "") {
        alert("Name cannot be empty.");
        return;
    }

    var course = prompt("Enter your course:");

    if (course === null) {
        return;
    }

    if (course.trim() === "") {
        alert("Course cannot be empty.");
        return;
    }

    var semester = prompt("Enter your semester:");

    if (semester === null) {
        return;
    }

    if (semester.trim() === "" || isNaN(semester) || semester < 1) {
        alert("Please enter a valid semester.");
        return;
    }

    var college = prompt("Enter your college:");

    if (college === null) {
        return;
    }

    if (college.trim() === "") {
        alert("College cannot be empty.");
        return;
    }

    var email = prompt("Enter your email:");

    if (email === null) {
        return;
    }

    if (email.trim() === "" || !email.includes("@") || !email.includes(".")) {
        alert("Please enter a valid email address.");
        return;
    }

    document.getElementById("studentName").textContent = name;
    document.getElementById("studentCourse").textContent = course;
    document.getElementById("studentSemester").textContent = "Semester : " + semester;
    document.getElementById("studentCollege").textContent = "College : " + college;
    document.getElementById("studentEmail").textContent = "Email : " + email;

    alert("Profile updated successfully!");
}