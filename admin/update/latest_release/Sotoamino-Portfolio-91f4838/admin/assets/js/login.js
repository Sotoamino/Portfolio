document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');

    form.addEventListener('submit', (e) => {
        const username = form.username.value.trim();
        const password = form.password.value;

        if (username === '' || password === '') {
            alert("Veuillez remplir tous les champs.");
            e.preventDefault();
        }

        // Bonus : feedback visuel
        form.querySelector('button').textContent = "Connexion...";
    });
});
