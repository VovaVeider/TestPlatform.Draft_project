const testContainer = document.getElementById('test-container');
const submitButton = document.getElementById('submit-button');

const jsonData = {
"questions": [
{
"text": "В каком городе родился А.П.Чехов?",
"answers": [
{
"text": "Новиград"
},
{
"text": "Санкт-Петербург"
},
{
"text": "Таганрог"
}
]
},
{
"text": "Кто написал Войну и мир?",
"answers": [
{
"text": "А.П.Чехов"
},
{
"text": "Глеб Жиглов"
},
{
"text": "Л.Н.Толстой"
}
]
}
]
};

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

let selectedAnswers = [];

submitButton.addEventListener('click', () => {
const data = {
"selected_answers": selectedAnswers
};

// Send data to server
console.log(data);
});