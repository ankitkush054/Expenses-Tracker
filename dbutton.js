const addIncomeBtn = document.getElementById('addIncomeBtn');
const addExpenseBtn = document.getElementById('addExpenseBtn');
const incomePopup = document.getElementById('incomePopup');
const expensePopup = document.getElementById('expensePopup');
const closeIncomePopup = document.getElementById('closeIncomePopup');
const closeExpensePopup = document.getElementById('closeExpensePopup');

function openIncomePopup() {
    incomePopup.style.display = 'block';
}

function openExpensePopup() {
    expensePopup.style.display = 'block';
}

function closePopup(popupElement) {
    popupElement.style.display = 'none';
}

addIncomeBtn.addEventListener('click', openIncomePopup);
addExpenseBtn.addEventListener('click', openExpensePopup);
closeIncomePopup.addEventListener('click', () => closePopup(incomePopup));
closeExpensePopup.addEventListener('click', () => closePopup(expensePopup));

// Close popup if user clicks outside the popup
window.addEventListener('click', (event) => {
    if (event.target === incomePopup) {
        closePopup(incomePopup);
    }
    if (event.target === expensePopup) {
        closePopup(expensePopup);
    }
});