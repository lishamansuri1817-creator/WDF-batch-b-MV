var themeButton = document.createElement("button");
themeButton.id = "themeButton";
document.body.appendChild(themeButton);
function setTheme(theme) {
    if (theme == "dark") {
        document.body.classList.add("dark-mode");
        themeButton.innerText = "☀️ Light Mode";
    }
    else {
        document.body.classList.remove("dark-mode");
        themeButton.innerText = "🌙 Dark Mode";
    }
    localStorage.setItem("studentSphereTheme", theme);
}
var savedTheme = localStorage.getItem("studentSphereTheme");
if (savedTheme == "dark") {
    setTheme("dark");
}
else {
    setTheme("light");
}
themeButton.addEventListener("click", function() {
    if (document.body.classList.contains("dark-mode")) {
        setTheme("light");
    }
    else {
        setTheme("dark");
    }
});