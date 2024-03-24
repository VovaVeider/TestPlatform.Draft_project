let jwt = getCookie('jwt');
let role = 'admin';
if (role !== 'admin') {
    //редирект
}

let preview;
const urlParams = new URLSearchParams(window.location.search);
let test_id = urlParams.get('id');
let edit_mode = false;
if (test_id) {
    test_id = parseInt(test_id);
    edit_mode = true;
}

if (edit_mode) {
    init_categories().then(init_page_from_data);
} else {
    init_categories()
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function init_categories() {
    let api_url = '/api/categories';
    return fetch(api_url)
        .then(response => response.json())
        .then(data => {
            data.categories.forEach((el, id) => {
                let opt = document.createElement('option');
                opt.value = el.id;
                opt.text = el.name;
                document.getElementById('test-category').appendChild(opt);
            })

        })
        .catch(error => {
            // Обработка ошибок
            console.error("Ошибка:", error);
        });
}

function init_page_from_data() {
    let api_url = '/api/tests/' + test_id + '?full=true';
    var requestOptions = {
        method: "GET", headers: {
            "Content-Type": "application/json", 'Authorization': jwt
        }
    };
    return fetch(api_url, requestOptions)
        .then(response => response.json())
        .then(data => {
            if (data.error === null) {
                document.getElementById('test-name').value = data.name;
                document.getElementById('test-description').value = data.description;
                let options = document.getElementById('test-category').children;
                for (let i = 0; i < options.length; i++) {
                    if (parseInt(options[i].value) === data.category) {
                        options[i].selected = true;
                    }
                }
                if (data.preview) {
                    const previewContainer = document.getElementById('preview-container');
                    const previewImage = document.createElement('img');
                    previewImage.src = data.preview;
                    previewImage.style.maxWidth = '100%';
                    previewImage.style.maxHeight = '100%';
                    previewContainer.innerHTML = ''; // Clear previous content
                    previewContainer.appendChild(previewImage);
                }




                // Add questions and answers from data
                data.test_body.questions.forEach((questionData, i) => {
                    addQuestion(questionData);
                    document.querySelectorAll('.question-container')[i].getElementsByClassName('question-text')[0].value = questionData.text;
                    // Add answers for the current question
                    questionData.answers.forEach((answerData, j) => {
                        addAnswer(document.querySelectorAll('.question-container')[i].querySelector('.toggleQuestion'));
                        const answerContainers = document.querySelectorAll('.question-container')[i].querySelectorAll('.answer-container');
                        const lastAnswerContainer = answerContainers[answerContainers.length - 1];
                        lastAnswerContainer.querySelector('.answer-text').value = answerData.text;
                        lastAnswerContainer.querySelector('.answer-points').value = answerData.points;
                    });
                });

                // Add results
                data.test_body.results.forEach((resultData) => {
                    addResult(resultData);
                });

            } else {
                // Handle redirect
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
}



function switchTab(tab) {
    let containers = document.getElementById('content-container').children;
    let tabs = document.getElementById('tabs-bar').children;

    let selectedContainerId = tab.id.replace('-tab', '-container');
    for (let i = 0; i < containers.length; i++) {
        let el = containers[i];
        if (el.id === selectedContainerId) {
            el.classList.remove('noactive-container');
            el.classList.add('active-container');
        } else {
            el.classList.remove('active-container');
            el.classList.add('noactive-container');
        }
    }
    for (let i = 0; i < tabs.length; i++) {
        let cur_tab = tabs[i];
        if (cur_tab.id === tab.id) {
            cur_tab.classList.add('active-tab');
        } else {
            cur_tab.classList.remove('active-tab');
        }
    }
}

function addQuestion() {
    const questionsContainer = document.getElementById('questions-container');
    const questionContainer = document.createElement('div');
    questionContainer.classList.add('question-container', 'tab-container');
    questionContainer.innerHTML = `
            <label>Текст вопроса:</label>
            <textarea type="text" class="question-text" placeholder="Введите текст вопроса"></textarea>
            <button class="removeQuestion" onclick="removeQuestion(this)">Удалить вопрос</button>
            <button class="toggleQuestion" onclick="toggleQuestion(this)">Показать ответы</button>
            <div class="answers-container" style="display: none;">
                <button class="addAnswer" onclick="addAnswer(this)">Добавить ответ</button>
            </div>
        `;

    questionsContainer.appendChild(questionContainer);
}

function toggleQuestion(button) {
    const questionContainer = button.closest('.question-container');
    const answersContainer = questionContainer.querySelector('.answers-container');
    const toggleText = button.innerHTML.includes('Показать') ? 'Скрыть' : 'Показать';

    if (answersContainer.style.display === 'none' || answersContainer.style.display === '') {
        answersContainer.style.display = 'block';
        button.innerHTML = `${toggleText} ответы`;
    } else {
        answersContainer.style.display = 'none';
        button.innerHTML = `${toggleText} ответы`;
    }
}

function addAnswer(button) {
    const answersContainer = button.closest('.question-container').querySelector('.answers-container');
    const answerContainer = document.createElement('div');
    answerContainer.classList.add('answer-container');

    answerContainer.innerHTML = `
        <label>Текст ответа:</label>
        <textarea class="answer-text" placeholder="Введите текст ответа"></textarea>

        <label>Баллы:</label>
        <input type="number" pattern="(-|\\+)?\\d+"  class="answer-points" placeholder="Введите баллы">

        <button class="removeAnswer" onclick="removeAnswer(this)">Удалить ответ</button>
      `;

    answersContainer.appendChild(answerContainer);
}


function removeQuestion(button) {
    const questionContainer = button.closest('.question-container');
    questionContainer.remove();
}

function removeAnswer(button) {
    const answerContainer = button.closest('.answer-container');
    answerContainer.remove();
}

function removeResult(button) {
    const resultContainer = button.closest('.result-container');
    resultContainer.remove();

    // Update scores without generating JSON
    updateResultsList();
}

function addResult(data = null) {

    const resultsContainer = document.getElementById('results-container');
    const resultContainer = document.createElement('div');
    resultContainer.classList.add('result-container', 'tab-container');
    resultContainer.innerHTML = `
                <label>Текст результата:</label>
                <textarea  class="result-text" placeholder="Введите текст результата">
                </textarea>
                <div class = 'max-score-div'>
                <label class ='max-result-label'>Максимальный балл:</label>
                <input type="number" class="max-score" placeholder="Введите максимальный балл">
                </div>

                <div class = 'min-score-div'>
                <label class ='min-result-label'>Минимальный балл:</label>
                <input type="number" class="min-score" placeholder="Введите минимальный балл">
                </div>

                <button class="removeResult" onclick="removeResult(this)">Удалить результат</button>
            `;
    if (data) {
        let max_score = data["max-score"];
        let min_score = data["min-score"];
        let text = data["text"];
        resultContainer.getElementsByClassName('min-score')[0].value = min_score
        resultContainer.getElementsByClassName('max-score')[0].value = max_score
        resultContainer.getElementsByClassName('result-text')[0].value = text;

    }
    resultsContainer.appendChild(resultContainer);
    updateResultsList();
}

function updateResultsList() {
    const resultContainers = document.querySelectorAll('.result-container');
    resultContainers.forEach((resultContainer, index) => {
        const minScoreDiv = resultContainer.querySelector('.min-score-div');
        const maxScoreDiv = resultContainer.querySelector('.max-score-div');

        if (resultContainers.length === 1) {
            minScoreDiv.style.display = 'none';
            maxScoreDiv.style.display = 'none';

        } else {
            if (index !== resultContainers.length - 1) {
                minScoreDiv.style.display = 'none';
                maxScoreDiv.style.display = 'block';

            } else {
                minScoreDiv.style.display = 'none';
                maxScoreDiv.style.display = 'none';
            }
        }
    });
}

function generateJSON() {
    const test = {
        questions: [], results: [],
    };

    const questionContainers = document.querySelectorAll('.question-container');
    questionContainers.forEach(questionContainer => {
        const question = {
            text: questionContainer.querySelector('.question-text').value, answers: [],
        };

        const answerContainers = questionContainer.querySelectorAll('.answer-container');
        answerContainers.forEach(answerContainer => {
            const answer = {
                text: answerContainer.querySelector('.answer-text').value,
                points: parseInt(answerContainer.querySelector('.answer-points').value),
            };

            question.answers.push(answer);
        });

        test.questions.push(question);
    });

    const resultContainers = document.querySelectorAll('.result-container');
    resultContainers.forEach((resultContainer, index) => {
        const result = {
            text: resultContainer.querySelector('.result-text').value,
            'min-score': index === 0 ? null : (parseInt(resultContainers[index - 1].querySelector('.max-score').value) + 1),
            'max-score': index === resultContainers.length - 1 ? null : parseInt(resultContainer.querySelector('.max-score').value),
        };

        test.results.push(result);
    });

    return test;
}

function previewImage() {
    const previewContainer = document.getElementById('preview-container');
    const fileInput = document.getElementById('test-preview');
    const file = fileInput.files[0];
    preview = file;
    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const previewImage = document.createElement('img');
            previewImage.src = e.target.result;
            previewImage.style.maxWidth = '100%';
            previewImage.style.maxHeight = '100%';
            previewContainer.innerHTML = ''; // Clear previous content
            previewContainer.appendChild(previewImage);
            previewImage.onload = function () {
                if (previewImage.width > 200 || previewImage.height > 200) {
                    previewContainer.innerHTML = ''; // Clear previous content
                    openModal('Изображение не боьше 200 на 200 пикселей.');
                }
            };

        };
        reader.readAsDataURL(file);
    }
}

function validateTest(testJson) {
    try {
        var testObject = testJson;

        // Проверка минимум одного вопроса
        if (!testObject.questions || testObject.questions.length < 1) {
            return "Ошибка: Минимум один вопрос должен быть.";
        }

        for (var i = 0; i < testObject.questions.length; i++) {
            var question = testObject.questions[i];

            // Проверка минимум двух ответов на каждый вопрос
            if (!question.answers || question.answers.length < 2) {
                return "Ошибка: На каждый вопрос должно быть минимум два ответа.";
            }

            for (var j = 0; j < question.answers.length; j++) {
                var answer = question.answers[j];

                // Проверка на целое число баллов за ответ
                if (!Number.isInteger(answer.points)) {
                    return "Ошибка: Балл за ответ должен быть целым числом.";
                }
            }
        }

        // Проверка минимум одного результата
        if (!testObject.results || testObject.results.length < 1) {
            return "Ошибка: Минимум один результат должен быть.";
        }

        for (var k = 0; k < testObject.results.length; k++) {
            var result = testObject.results[k];

            // Проверка, что текст результата не пустой
            if (!result.text) {
                return "Ошибка: Текст результата не должен быть пустым.";
            }

            // Установка минимального и максимального балла
            if (k === 0) {
                result_min_score = null;
            } else {
                result_min_score = testObject.results[k - 1]["max-score"] + 1;
            }

            result_max_score = result["max-score"];

            // if (result_max_score === null && k !== testObject.results.length - 1) {
            //     return "Ошибка: Максимальный балл для результата не может быть None, кроме последнего.";
            // }

            if (result_min_score !== null && result_max_score !== null && result_min_score > result_max_score) {
                return "Ошибка: Диапазоны не должны перекрывать друг друга";
            }
        }

        // Если все проверки пройдены успешно
        return "OK";

    } catch (error) {
        return "Ошибочный формат JSON.";
    }
}

function sendTest() {
    const testJson = generateJSON();
    const validationResult = validateTest(testJson);
    if (validationResult !== 'OK') {
        openModal(validationResult);
    } else {
        openModal('Отправка на сервер, ждите', false);

        let api_url = '/api/tests'
        if (edit_mode) {
            api_url += '/' + test_id;
        }
        var requestOptions = {
            method: (edit_mode) ? 'PUT' : "POST", headers: {
                'Authorization': jwt
            }, body: JSON.stringify({
                'test_body': testJson,
                'name': document.getElementById('test-name').value,
                'description': document.getElementById('test-description').value,
                'category': parseInt(document.getElementById('test-category').value)
            })
        };

        fetch(api_url, requestOptions)
            .then(response => response.json())
            .then(data => {
                if (data.error !== null) {
                    closeModal();
                    openModal(data.error_descr)

                }
                if (preview) {
                    let formData = new FormData();
                    formData.append('image', preview);
                    requestOptions = {
                        method: "POST", headers: {
                            'Authorization': jwt
                        }, body: formData
                    };

                    fetch('/api/tests/' + ((edit_mode) ? test_id : data.id) + '/preview', requestOptions)
                        .then(response => {
                            if (!edit_mode) {
                                window.location.href = '/admin_test.html?id=' + data.id;
                            }
                            closeModal();
                        })
                        .catch(error => {
                            console.error('Произошла ошибка при отправке запроса:', error);
                        });

                } else {
                    if (!edit_mode) {
                        window.location.href = '/admin_test.html?id=' + data.id;
                    }
                    closeModal();
                }
            })
            .catch(error => {
                // Обработка ошибок
                console.error("Ошибка:", error);
            });


    }
}

function openModal(message, closeable = true) {
    let modal = document.getElementById('modal');
    modal.style.display = 'flex';
    document.getElementById('modal-msg').innerText = message;
    if (!closeable) {
        document.getElementById('closeModal').style.display = 'none';
    } else {
        document.getElementById('closeModal').style.display = 'inline';
    }
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}