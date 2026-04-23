function generateCard() {
    document.getElementById("cardName").innerText =
        document.getElementById("name").value;

    document.getElementById("cardTitle").innerText =
        document.getElementById("title").value;

    document.getElementById("cardEmail").innerText =
        document.getElementById("email").value;

    document.getElementById("cardPhone").innerText =
        document.getElementById("phone").value;
}