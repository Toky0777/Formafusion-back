const select_month = document.querySelector('#select_month');
const month = [document.querySelectorAll(`.month_0`),
document.querySelectorAll(`.month_1`),
document.querySelectorAll(`.month_2`),
document.querySelectorAll(`.month_3`),
document.querySelectorAll(`.month_4`),
document.querySelectorAll(`.month_5`),
document.querySelectorAll(`.month_6`),
document.querySelectorAll(`.month_7`),
document.querySelectorAll(`.month_8`),
document.querySelectorAll(`.month_9`),
document.querySelectorAll(`.month_10`),
document.querySelectorAll(`.month_11`),];

month[0].className = 'table-row';

select_month.addEventListener('change', () => {
    if (select_month.value == 12) {
        for (let i = 0; i < month.length; i++) {
            const all_month = month[i]
            if (all_month instanceof NodeList) {
                for (let x = 0; x < all_month.length; x++) {
                    all_month[x].className = 'table-row';
                }
            } else {
                all_month.className = 'table-row';
            }
        }
    } else {
        for (let i = 0; i < month.length; i++) {
            const all_month = month[i]
            if (all_month instanceof NodeList) {
                for (let x = 0; x < all_month.length; x++) {
                    all_month[x].className = 'hidden';
                }
            } else {
                all_month.className = 'hidden';
            }
        }

        const month_selected = month[select_month.value];

        if (month_selected instanceof NodeList) {
            for (let u = 0; u < month_selected.length; u++) {
                month_selected[u].className = 'table-row';
            }
        } else {
            month_selected.className = 'table-row';
        }
    }

})