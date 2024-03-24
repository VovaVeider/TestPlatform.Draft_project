const testContainer = document.getElementById('test-container');
const submitButton = document.getElementById('submit-button');

let jwt = getCookie('jwt');
const urlParams = new URLSearchParams(window.location.search);
let urlParam = urlParams.get('id');
// Определение заголовков
const headers = new Headers();
headers.append('Content-Type', 'application/json');
headers.append('Authorization', jwt); // Пример для использования токена авторизации

// Определение объекта настроек для fetch
const requestOptions = {
    method: 'GET', // Метод запроса (GET, POST, PUT, DELETE и т.д.)
    headers: headers, // Передача объекта заголовков
    // Другие параметры, если необходимо
};
$jsonData = fetch(`/api/tests/${urlParam}`, requestOptions,)
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        init_page(data.test_body);
    });

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function show_result(result) {
    testContainer.innerHTML = '<h2>Ваш результат</h2> <br> '+ result;
    document.getElementById('submit-button').remove();
}

function init_page(jsonData) {
    var ret_btn = document.createElement('button');
    ret_btn.className='menu-button';
    ret_btn.textContent='Главная';
    ret_btn.addEventListener('click',()=>{window.location.href = 'index.html';});

    var main_div = document.getElementsByTagName('main')[0];
    main_div.prepend(ret_btn);



    const questions = jsonData.questions;

// Create a form for each question
    questions.forEach((question, index) => {
        const form = document.createElement('form');
        form.id = `question-${index}`;

        const questionElement = document.createElement('h2');
        questionElement.textContent = question.text;
        form.appendChild(questionElement);

// Create a radio button for each answer choice
        question.answers.forEach((answer, answerIndex) => {
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = `question-${index}`;
            radio.value = answerIndex;
            radio.id = `answer-${index}-${answerIndex}`;

            const label = document.createElement('label');
            label.htmlFor = `answer-${index}-${answerIndex}`;
            label.textContent = answer.text;

            const div = document.createElement('div');
            div.appendChild(radio);
            div.appendChild(label);
            form.appendChild(div);
        });

        testContainer.appendChild(form);
    });

// Handle user input
    const answerForms = document.querySelectorAll('form');
    answerForms.forEach((form, index) => {
        form.addEventListener('change', () => {
            const selectedAnswer = form.querySelector('input[type="radio"]:checked');
            if (selectedAnswer) {
                selectedAnswers[index] = parseInt(selectedAnswer.value);
            }
        });
    });

    function display_modal() {
        const modal = document.getElementById('modalNotAllQuestions');
        const closeModalBtn = document.getElementById('closeModalBtn');

        modal.style.display = 'block';

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    let selectedAnswers = new Array(jsonData.questions.length).fill(null);
    var result_is_get = false
    submitButton.addEventListener('click', () => {
        let not_select = selectedAnswers.some((elem, index) => elem === null);
        if (not_select || result_is_get) {
            if (not_select) {
                display_modal();
            }
        } else {
            const requestOptions = {
                method: 'POST', // Метод запроса (GET, POST, PUT, DELETE и т.д.)
                headers: headers, // Передача объекта заголовков
                body: JSON.stringify({"results": selectedAnswers})
                // Другие параметры, если необходимо
            };
            result_is_get = true;
            $jsonData = fetch(`/api/tests/${urlParam}/result`, requestOptions,)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    show_result(data.result);
                });



        }

    });
}
