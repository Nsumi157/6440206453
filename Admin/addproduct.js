
    function selectColor(color) {
        // Remove 'selected' class from all color boxes
        const colorBoxes = document.querySelectorAll('.color-box');
        colorBoxes.forEach(box => {
            box.classList.remove('selected');
        });

        // Add 'selected' class to the clicked color box
        const clickedColorBox = document.querySelector(`.color-box[style*="${color}"]`);
        clickedColorBox.classList.add('selected');

        // Update any logic you need with the selected color here
        console.log('Selected color:', color);
    }

