
const formulaire_inputs = document.querySelectorAll('.formulaire_input');


formulaire_inputs.forEach(parent => {
    const select_option = document.querySelectorAll(`.input_${parent.id}`);
    select_option.forEach(option => {
        option.addEventListener('click', () => {
            const title = parent.children[0];
            const input = parent.children[1];
            const optionValue = option.getAttribute('data-value');
            input.value = optionValue;
            title.innerText = option.innerText;
        })
    });
});


