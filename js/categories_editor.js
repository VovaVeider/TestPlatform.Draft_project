
let categories = null;
let jwt = getCookie('jwt');

const categoriesList = document.getElementById('categories-list');
let editingCategoryId = null;
let curr_old_name = null;
fetchCategories();


///Ф-ции
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}
function fetchCategories() {
    fetch('/api/categories', {
        headers: {
            'Authorization': jwt,
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.error === null) {
                categories = data.categories;
                displayCategories(categories);
            } else {
                console.error("Error fetching categories:", data.error_descr);
            }
        })
        .catch(error => {
            console.error("Error fetching categories:", error);
        });
}

function displayCategories(categories) {
    categoriesList.innerHTML = '';
    categories.forEach(category => {
        const listItem = document.createElement('li');
        listItem.id = 'category_' + category.id;
        listItem.innerHTML = ` <input  "type="text" value="${category.name}" readonly>
                                      <button  class = "edit-btn" onclick="startEditing(${category.id})">Переименовать</button>
                                      <button  class = "confirm-btn" style="display: none" onclick="confirmEditing(${category.id})">ОК</button>
                                      <button class = "cancel-btn" style="display: none" onclick="cancelEditing()">Отмена</button>
                                      <button class = "delete-btn" onclick="deleteCategory(${category.id})">Удалить</button> `;
        categoriesList.appendChild(listItem);
    });
    curr_old_name = null;
}

function addCategory() {
    const newCategoryInput = document.getElementById('new-category');
    const newName = newCategoryInput.value.trim();

    if (newName.trim() === '') {
        alert('Введите имя категории.');
        return;
    }

    const existingCategories = Array.from(categories).map(el => el.name);
    if (existingCategories.includes(newName)) {
        alert('Категория с таким именем уже существует.');
        return;
    }

    fetch('/api/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': jwt,
        },
        body: JSON.stringify({name: newName}),
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.error === null) {
                fetchCategories();
            } else {
                if (data.error === 'CAT_EXISTS')
                    fetchCategories();
                alert('Категория с таким именем уже существует.');
            }
        })
        .catch(error => {
            console.error("Error adding category:", error);
        });
}

function startEditing(categoryId) {
    if (curr_old_name !== null) {
        cancelEditing();
    }
    let category = categoriesList.querySelector(`li#category_${categoryId}`);
    let category_input = category.querySelector('input');
    let category_name = category_input.value;
    curr_old_name = category_name;
    category_input.removeAttribute('readonly');
    category_input.focus();

    category.querySelector('.edit-btn').style.display = 'none';
    category.querySelector('.confirm-btn').style.display = '';
    category.querySelector('.cancel-btn').style.display = '';
}

function confirmEditing(category_id) {
    const listItem = categoriesList.querySelector(`li input:not([readonly])`);
    const newName = listItem.value.trim();

    if (newName === '') {
        alert('Введите НЕ ПУСТОЕ имя категории.');
        return;
    }

    const existingCategories = Array.from(categories).map(el => el.name);
    if (existingCategories.includes(newName)) {
        alert('Категория с таким именем уже существует.');
        return;
    }

    fetch(`/api/categories/${category_id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': jwt,
        },
        body: JSON.stringify({name: newName}),
    })
        .then(response => response.json())
        .then(data => {
            curr_old_name = null;
            fetchCategories();
        })
        .catch(error => {
            console.error("Error renaming category:", error);
        });
}

function cancelEditing() {
    const input = categoriesList.querySelector(`li input:not([readonly])`);
    if (input !== null) {
        const listItem = input.parentElement;
        if (curr_old_name !== null) {
            input.value = curr_old_name;
        }
        input.setAttribute('readonly', true);
        listItem.querySelector('.edit-btn').style.display = '';
        listItem.querySelector('.confirm-btn').style.display = 'none';
        listItem.querySelector('.cancel-btn').style.display = 'none';
    }
}

function deleteCategory(categoryId) {
    fetch(`/api/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': jwt,
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.error === null) {
                fetchCategories();
            } else {
                console.error("Error deleting category:", data.error_descr);
            }
        })
        .catch(error => {
            console.error("Error deleting category:", error);
        });
}

