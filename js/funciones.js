//crear evento para que se ejecute el código cuando haya terminado de cargarse el DOM
document.addEventListener('DOMContentLoaded', () => {

    const themeSwitcher = document.getElementById('theme-switcher');
    const html = document.documentElement;

    // Cargar el tema guardado
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'light') {
        html.classList.remove('dark');
        themeSwitcher.textContent = '🌙';
    } else {
        html.classList.add('dark');
        themeSwitcher.textContent = '☀️';
    }

    themeSwitcher.addEventListener('click', () => {
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            themeSwitcher.textContent = '🌙';
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            themeSwitcher.textContent = '☀️';
            localStorage.setItem('theme', 'dark');
        }
    });

    const url = 'api/index.php/libros';

    fetch(url)
    .then(response => response.text())
    .then(text => {
        let jsonString = text.trim();
        if (jsonString.startsWith('Array{')) {
            jsonString = jsonString.substring(5);
        }
        const data = JSON.parse(jsonString);
        mostrarLibros(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
})

function mostrarLibros(datos){

    const libros = datos.data;
    const divLibros = document.getElementById('divLibros');

    if(datos.success && datos.count > 0){
        divLibros.innerHTML = ''; // Limpiar el contenedor
        libros.forEach(libro => {
            const libroCard = document.createElement('div');
            libroCard.className = 'bg-white dark:bg-neutral-900 rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300';

            const resumenCorto = libro.resumen.substring(0, 40);

            libroCard.innerHTML = `
                <img src="img/peques/${libro.imagen}" alt="${libro.titulo}" data-id="${libro.id}" class="w-full h-64 object-cover cursor-pointer" onerror="this.onerror=null;this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=IMAGEN+NO+DISPONIBLE';">
                <div class="p-6">
                    <h3 class="font-bebas text-2xl text-black dark:text-white mb-2">${libro.titulo}</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 resumen">${resumenCorto}...</p>
                    <a href="#" class="text-purple-600 dark:text-purple-400 hover:underline mt-2 inline-block ver-mas">Ver más</a>
                </div>
            `;

            divLibros.appendChild(libroCard);

            const verMasLink = libroCard.querySelector('.ver-mas');
            const resumenP = libroCard.querySelector('.resumen');
            verMasLink.addEventListener('click', (e) => {
                e.preventDefault();
                if (resumenP.textContent === `${resumenCorto}...`) {
                    resumenP.textContent = libro.resumen;
                    verMasLink.textContent = 'Ver menos';
                } else {
                    resumenP.textContent = `${resumenCorto}...`;
                    verMasLink.textContent = 'Ver más';
                }
            });
        });

        divLibros.addEventListener('click', (e) => {
            if (e.target.tagName === 'IMG') {
                const libroId = e.target.dataset.id;
                mostrarModal(libros.find(l => l.id == libroId));
            }
        });

    }else if(datos.count == 0){
        divLibros.innerHTML = "<p class=\"text-center text-xl text-neutral-500 col-span-full\">No hay libros disponibles.</p>";
    }
}

function mostrarModal(libro) {
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');
    const modalBody = document.getElementById('modal-body');
    const closeButton = modal.querySelector('.close');

    modalBody.innerHTML = `
        <h2 class="font-bebas text-4xl text-black dark:text-white mb-4">${libro.titulo}</h2>
        <p class="text-lg text-neutral-700 dark:text-neutral-300 mb-2"><strong>Autor:</strong> ${libro.autor}</p>
        <p class="text-lg text-neutral-700 dark:text-neutral-300 mb-4"><strong>Año de Publicación:</strong> ${libro.fecha_publicacion}</p>
        <p class="text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">${libro.resumen}</p>
    `;

    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const closeModal = () => {
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    closeButton.onclick = closeModal;
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
}
