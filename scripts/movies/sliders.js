(function() {
    'use strict';

    const ratingSlider = document.getElementById('rating_slider');
    const ratingSliderBox = document.getElementById('rating_slider_box');
    const ratingMinInput = document.getElementById('rating_min');
    const ratingMaxInput = document.getElementById('rating_max');
    const ratingMinVal = document.getElementById('rating_min_val');
    const ratingMaxVal = document.getElementById('rating_max_val');
    const yearSlider = document.getElementById('year_slider');
    const yearSliderBox = document.getElementById('year_slider_box');
    const yearMinInput = document.getElementById('year_min');
    const yearMaxInput = document.getElementById('year_max');
    const yearMinVal = document.getElementById('year_min_val');
    const yearMaxVal = document.getElementById('year_max_val');

    if (!ratingSlider || !yearSlider) {
        return;
    }

    let ratingMin = parseFloat(ratingMinInput.value) || 0.0;
    let ratingMax = parseFloat(ratingMaxInput.value) || 10.0;

    noUiSlider.create(ratingSlider, {
        start: [ratingMin, ratingMax],
        connect: true,
        step: 0.1,
        range: { min: 0, max: 10 },
        tooltips: [true, true],
        format: {
            to: v => parseFloat(v).toFixed(1),
            from: v => parseFloat(v)
        }
    });

    ratingSlider.noUiSlider.on('update', function(values) {
        ratingMinVal.textContent = values[0];
        ratingMaxVal.textContent = values[1];
        ratingMinInput.value = values[0];
        ratingMaxInput.value = values[1];
    });

    const labelRating = document.getElementById('label_rating');
    const ratingRange = document.getElementById('rating_range');
    if (labelRating && ratingRange) {
        const toggleRatingSlider = function(e) {
            ratingSliderBox.style.display = ratingSliderBox.style.display === 'block' ? 'none' : 'block';
            yearSliderBox.style.display = 'none';
            e.stopPropagation();
        };
        labelRating.onclick = toggleRatingSlider;
        ratingRange.onclick = toggleRatingSlider;
    }

    document.addEventListener('click', function() {
        ratingSliderBox.style.display = 'none';
    });

    let yearMin = parseInt(yearMinInput.value) || 1900;
    let yearMax = parseInt(yearMaxInput.value) || 2025;

    noUiSlider.create(yearSlider, {
        start: [yearMin, yearMax],
        connect: true,
        step: 1,
        range: { min: 1900, max: 2025 },
        tooltips: [true, true],
        format: {
            to: v => parseInt(v),
            from: v => parseInt(v)
        }
    });

    yearSlider.noUiSlider.on('update', function(values) {
        yearMinVal.textContent = values[0];
        yearMaxVal.textContent = values[1];
        yearMinInput.value = values[0];
        yearMaxInput.value = values[1];
    });

    const labelYear = document.getElementById('label_year');
    const yearRange = document.getElementById('year_range');
    if (labelYear && yearRange) {
        const toggleYearSlider = function(e) {
            yearSliderBox.style.display = yearSliderBox.style.display === 'block' ? 'none' : 'block';
            ratingSliderBox.style.display = 'none';
            e.stopPropagation();
        };
        labelYear.onclick = toggleYearSlider;
        yearRange.onclick = toggleYearSlider;
    }

    document.addEventListener('click', function() {
        yearSliderBox.style.display = 'none';
    });

    ratingSliderBox.onclick = function(e) { e.stopPropagation(); };
    yearSliderBox.onclick = function(e) { e.stopPropagation(); };
})();

