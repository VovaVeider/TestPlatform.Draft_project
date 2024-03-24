document.addEventListener('DOMContentLoaded', checkAuthentication);
let currentPage = 1;

// Функция для запроса данных с параметрами
function fetchDataWithParams(page = 1) {
    currentPage = page;
    const searchInput = document.querySelector(".search-bar .header-nav-right-search-container input");
    const onlyFavoritesCheckbox = document.querySelector(".search-bar input[type='checkbox'][value='favorites']");
    const sortByCheckbox = document.querySelector(".search-bar input[type='checkbox'][value='sortBy']");

    const searchParam = searchInput.value ? `starts-with=${searchInput.value}` : '';
    const favoriteParam = onlyFavoritesCheckbox && onlyFavoritesCheckbox.checked ? 'favorite=true' : '';
    const sortParam = sortByCheckbox && !sortByCheckbox.checked ? 'sort=DESC' : '';
    let categories = parseInt(document.getElementById('categories').value)
    if (categories >= -1) {
        categories = `categories=[${categories}]`;
    } else {
        categories = '';
    }
    const queryParams = [searchParam, favoriteParam, sortParam, categories].filter(param => param !== '').join('&');
    const fullURL = `/api/tests?page=${page}&amount=8&${queryParams}`;
    const jwt = getCookie('jwt');
    const headers = new Headers();
    headers.append('Content-Type', 'application/json');
    if (jwt !== undefined) {
        headers.append('Authorization', jwt); // Пример для использования токена авторизации
    }

    fetch(fullURL, {
        method: 'GET',
        headers: headers
    })
        .then(response => response.json())
        .then(data => {
            // Очищаем контейнер перед добавлением новых карточек
            const testCardsContainer = document.getElementById("testCardsContainer");
            testCardsContainer.innerHTML = '';

            data.tests.forEach(test => {
                const card = document.createElement("div");
                card.classList.add("card");
                card.id = 'test_' + test.id;
                const img = document.createElement("img");
                img.src = test.preview || "../api/previews/standart_photo.jpg"; // Используем standart_photo, если нет изображения
                img.alt = "Фото";
                card.appendChild(img);

                const h3 = document.createElement("h3");
                h3.textContent = test.name;
                card.appendChild(h3);

                const p = document.createElement("p");
                const description = test.description;

                // Обрезаем текст до первых 200 символов
                const truncatedDescription = description.length > 150 ? description.substring(0, 150) + '...' : description;

                p.textContent = truncatedDescription;
                card.appendChild(p);

                const cardButtons = document.createElement("div");
                cardButtons.classList.add("card-buttons");

                // Проверяем наличие роли в cookies
                const userRole = getCookie("role");

                if (userRole === "admin" || userRole === 'user') {
                    const twoButtons = document.createElement("div");
                    twoButtons.classList.add("two-buttons");
                    cardButtons.appendChild(twoButtons);
                    const buttonStart = document.createElement("button");
                    buttonStart.textContent = "Пройти";
                    buttonStart.classList.add("start-test-button");
                    twoButtons.appendChild(buttonStart);

                    buttonStart.addEventListener("click", () => {
                        window.location.href = `solve_test.html?id=${test.id}`
                    });
                    const buttonFavorite = document.createElement("button");
                    buttonFavorite.classList.add("block-button-favorite");
                    twoButtons.appendChild(buttonFavorite);
                    if (test.favorite === true) {
                        buttonFavorite.classList.add('selected-favorite');
                    } else {
                        buttonFavorite.classList.add('unselected-favorite');
                    }
                    if (isTouchDevice()) {
                        buttonFavorite.addEventListener("touchstart", (event) => {
                            event.preventDefault(); // Prevent default touch behavior
                            addToFavorite(test.id, buttonFavorite);
                        });
                    } else {
                        buttonFavorite.addEventListener("click", (event) => {
                            addToFavorite(test.id, buttonFavorite);
                        });
                    }
                }


                    if (userRole === "admin") {
                        const buttonEdit = document.createElement("button");
                        buttonEdit.textContent = "Изменить";
                        cardButtons.appendChild(buttonEdit);
                        buttonEdit.addEventListener("click", () => {
                            window.location.href = `admin_test.html?id=${test.id}`;
                        });

                        const buttonDelete = document.createElement("button");
                        buttonDelete.textContent = "Удалить";
                        cardButtons.appendChild(buttonDelete);
                        buttonDelete.addEventListener("click", () => {
                            alert('Удалено.');
                            deleteTest(test.id);
                        });

                    }

                    if (userRole === undefined) {
                        const authMessage = document.createElement("div");
                        authMessage.classList.add("card-auth-message"); // Добавляем класс стиля
                        authMessage.innerHTML = '<p>Авторизуйтесь для того, чтобы пройти тест.</p>';
                        cardButtons.appendChild(authMessage);
                    }
                    const watchesContainer = document.createElement("div");
                    const watchesImage = document.createElement('img');
                    const watchesCount = document.createElement('span');
                    watchesContainer.style.display = 'flex';
                    watchesContainer.style.justifyContent = 'flex-end';
                    watchesCount.innerText = test.passed;
                    watchesImage.src = 'media/images/galka.png';
                    watchesImage.style.maxWidth = '20px';
                    watchesImage.style.maxHeight = '20px';
                    watchesImage.style.marginRight = '7px';
                    watchesImage.style.marginBottom = '0px';
                    watchesContainer.appendChild(watchesImage);
                    watchesContainer.appendChild(watchesCount);
                    cardButtons.appendChild(watchesContainer);

                    card.appendChild(cardButtons);
                    testCardsContainer.appendChild(card);
                }
            )
                ;

                // Создаем кнопки пагинации
                createPaginationButtons(data.pages);
                const selectedPagButton = document.querySelector(`#pag_${page}`);
                selectedPagButton.style.backgroundColor = '#0586e8';
                selectedPagButton.style.color = 'white';

            })
                .catch(error => {
                    console.error('Ошибка при загрузке данных с сервера:', error);
                });
        }

    function deleteTest(testId) {
        const jwt = getCookie('jwt');
        fetch(`/api/tests/${testId}`, {

            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': jwt
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.error == null) {
                    let cards = document.querySelectorAll('.card')
                    if (cards.length < 2 && currentPage !== 1) {
                        currentPage = -1;
                        fetchDataWithParams(currentPage);
                    } else {
                        document.querySelector('#test_' + testId).remove();
                    }
                } else {
                    alert('Ошибка.');
                }
            });
    }

    function addToFavorite(testId, buttonFavorite) {
        const jwt = getCookie('jwt');
        if (buttonFavorite.matches('.selected-favorite')) {
            fetch(`/api/users/me/favorites/${testId}`, {

                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': jwt
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error == null) {
                        buttonFavorite.classList.remove('selected-favorite');
                        buttonFavorite.classList.add('unselected-favorite');
                    } else {
                        alert('Ошибка.');
                    }
                });
        } else {

            // Формируем объект с данными для отправки
            var requestData = {
                "id": testId
            };

            // Отправляем POST запрос на ваш API
            fetch('/api/users/me/favorites', {

                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': jwt
                },
                body: JSON.stringify(requestData),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error == null) {
                        buttonFavorite.classList.remove('unselected-favorite');
                        buttonFavorite.classList.add('selected-favorite');
                    } else {
                        alert('Ошибка.');
                    }
                });
        }
    }


// Функция для создания кнопок пагинации
    function createPaginationButtons(pages) {
        const paginationContainer = document.querySelector(".pagination-items");

        // Очищаем контейнер перед добавлением новых кнопок
        paginationContainer.innerHTML = '';

        for (let i = 1; i <= pages; i++) {
            const button = document.createElement("button");
            button.id = 'pag_' + i;
            button.textContent = i;
            button.addEventListener("click", () => {
                // При клике на кнопку, делаем новый запрос с параметром page
                fetchDataWithParams(i);
            });
            paginationContainer.appendChild(button);
        }
    }

// Вызываем функцию с параметрами по умолчанию при загрузке страницы
    fetchDataWithParams();

// Добавляем обработчик события для кнопки поиска
    const searchButton = document.querySelector(".search-bar button");
    searchButton.addEventListener("click", () => {
        fetchDataWithParams();
    });

// Категории
    fetch('/api/categories')
        .then(response => response.json())
        .then(data => {
            // Получаем массив категорий
            const categories = data.categories;

            // Получаем существующий элемент <select>
            const selectElement = document.getElementById('categories');

            // Удаляем все текущие <option> элементы
            //selectElement.innerHTML = '';

            // Добавляем новые <option> элементы на основе данных с сервера
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Ошибка при загрузке категорий:', error);
        });

    function openLogin() {
        window.location.href = 'auth.html';
    }

    function checkAuthentication() {
        const jwtToken = getCookie('jwt');
        const login = getCookie('login');
        const role = getCookie('role');

        if (jwtToken && login) {
            document.getElementById('loginButton').style.display = 'none';
            document.getElementById('logoutButton').style.display = 'block';
            document.getElementById('userName').style.display = 'inline-block';
            document.getElementById('userName').style.margin = '0 auto';
            document.getElementById('userName').innerText = login;
        }

        if (role !== 'admin') {
            document.getElementById('headerNavLeftButtons').style.display = 'none';

            // document.getElementById('headerNav').style.paddingTop = '20px';
        }
    }

    function logout() {
        // Удаление куков jwt, role, id и login
        document.cookie = 'jwt=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'role=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'login=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';

        // Скрытие элемента userName
        document.getElementById('userName').style.display = 'none';

        // Показ элемента loginButton и скрытие logoutButton
        document.getElementById('loginButton').style.display = 'block';
        document.getElementById('logoutButton').style.display = 'none';
        location.reload();
        window.location.href = 'index.html';

    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function isTouchDevice() {
        return (('ontouchstart' in window) ||
            (navigator.maxTouchPoints > 0) ||
            (navigator.msMaxTouchPoints > 0));
    }