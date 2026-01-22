document.getElementById('loadData').addEventListener('click', async () => {
    const output = document.getElementById('output');
    output.innerHTML = 'Завантаження...';
    
    try {
        const response = await fetch('http://localhost:5000/api/hello');
        const data = await response.json();
        output.innerHTML = `<strong>Відповідь бекенду:</strong> ${data.message}`;
    } catch (error) {
        output.innerHTML = `Помилка: ${error.message}`;
    }
});