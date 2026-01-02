(function() {
    'use strict';

    const ratingDisplay = document.getElementById('rating_display');
    const yearDisplay = document.getElementById('year_display');
    const ratingModal = document.getElementById('ratingModal');
    const yearModal = document.getElementById('yearModal');
    const ratingMinInput = document.getElementById('rating_min');
    const ratingMaxInput = document.getElementById('rating_max');
    const yearMinInput = document.getElementById('year_min');
    const yearMaxInput = document.getElementById('year_max');

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(function() {
                modal.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            setTimeout(function() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    }

    function updateRatingDisplay() {
        const min = ratingMinInput.value;
        const max = ratingMaxInput.value;
        if (min && max) {
            const minVal = parseFloat(min);
            const maxVal = parseFloat(max);
            if (minVal === maxVal) {
                ratingDisplay.textContent = minVal.toFixed(1);
            } else {
                ratingDisplay.textContent = minVal.toFixed(1) + ' - ' + maxVal.toFixed(1);
            }
        } else {
            ratingDisplay.textContent = 'Todos';
        }
    }

    function updateYearDisplay() {
        const min = yearMinInput.value;
        const max = yearMaxInput.value;
        if (min && max) {
            const minVal = parseInt(min);
            const maxVal = parseInt(max);
            if (minVal === maxVal) {
                yearDisplay.textContent = minVal;
            } else {
                yearDisplay.textContent = minVal + ' - ' + maxVal;
            }
        } else {
            yearDisplay.textContent = 'Todos';
        }
    }

    if (ratingDisplay) {
        ratingDisplay.addEventListener('click', function() {
            const currentMin = ratingMinInput.value;
            const currentMax = ratingMaxInput.value;

            if (currentMin && currentMax) {
                const minVal = parseFloat(currentMin);
                const maxVal = parseFloat(currentMax);
                if (minVal === maxVal) {
                    document.getElementById('rating_type_single').checked = true;
                    document.getElementById('rating_single_value').value = currentMin;
                    document.getElementById('rating_range_inputs').style.display = 'none';
                    document.getElementById('rating_single_inputs').style.display = 'block';
                } else {
                    document.getElementById('rating_type_range').checked = true;
                    document.getElementById('rating_range_min').value = currentMin;
                    document.getElementById('rating_range_max').value = currentMax;
                    document.getElementById('rating_single_inputs').style.display = 'none';
                    document.getElementById('rating_range_inputs').style.display = 'block';
                }
            } else {
                document.getElementById('rating_type_single').checked = true;
                document.getElementById('rating_single_value').value = '';
                document.getElementById('rating_range_min').value = '';
                document.getElementById('rating_range_max').value = '';
                document.getElementById('rating_range_inputs').style.display = 'none';
                document.getElementById('rating_single_inputs').style.display = 'block';
            }

            openModal('ratingModal');
        });
    }

    if (yearDisplay) {
        yearDisplay.addEventListener('click', function() {
            const currentMin = yearMinInput.value;
            const currentMax = yearMaxInput.value;

            if (currentMin && currentMax) {
                const minVal = parseInt(currentMin);
                const maxVal = parseInt(currentMax);
                if (minVal === maxVal) {
                    document.getElementById('year_type_single').checked = true;
                    document.getElementById('year_single_value').value = currentMin;
                    document.getElementById('year_range_inputs').style.display = 'none';
                    document.getElementById('year_single_inputs').style.display = 'block';
                } else {
                    document.getElementById('year_type_range').checked = true;
                    document.getElementById('year_range_min').value = currentMin;
                    document.getElementById('year_range_max').value = currentMax;
                    document.getElementById('year_single_inputs').style.display = 'none';
                    document.getElementById('year_range_inputs').style.display = 'block';
                }
            } else {
                document.getElementById('year_type_single').checked = true;
                document.getElementById('year_single_value').value = '';
                document.getElementById('year_range_min').value = '';
                document.getElementById('year_range_max').value = '';
                document.getElementById('year_range_inputs').style.display = 'none';
                document.getElementById('year_single_inputs').style.display = 'block';
            }

            openModal('yearModal');
        });
    }

    document.querySelectorAll('[data-modal]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });

    document.querySelectorAll('.movies-filter-modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    document.getElementById('rating_type_single').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('rating_range_inputs').style.display = 'none';
            document.getElementById('rating_single_inputs').style.display = 'block';
        }
    });

    document.getElementById('rating_type_range').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('rating_single_inputs').style.display = 'none';
            document.getElementById('rating_range_inputs').style.display = 'block';
        }
    });

    document.getElementById('year_type_single').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('year_range_inputs').style.display = 'none';
            document.getElementById('year_single_inputs').style.display = 'block';
        }
    });

    document.getElementById('year_type_range').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('year_single_inputs').style.display = 'none';
            document.getElementById('year_range_inputs').style.display = 'block';
        }
    });

    document.getElementById('ratingModalApply').addEventListener('click', function() {
        const type = document.querySelector('input[name="rating_type"]:checked').value;
        if (type === 'single') {
            const value = document.getElementById('rating_single_value').value;
            if (value) {
                ratingMinInput.value = value;
                ratingMaxInput.value = value;
            } else {
                ratingMinInput.value = '';
                ratingMaxInput.value = '';
            }
        } else {
            const min = document.getElementById('rating_range_min').value;
            const max = document.getElementById('rating_range_max').value;
            ratingMinInput.value = min || '';
            ratingMaxInput.value = max || '';
        }
        updateRatingDisplay();
        closeModal('ratingModal');
        
        if (window.updateClearButton) {
            window.updateClearButton();
        }
        
        const filtrosForm = document.getElementById('filtrosForm');
        if (filtrosForm) {
            setTimeout(function() {
                filtrosForm.submit();
            }, 350);
        }
    });

    document.getElementById('yearModalApply').addEventListener('click', function() {
        const type = document.querySelector('input[name="year_type"]:checked').value;
        if (type === 'single') {
            const value = document.getElementById('year_single_value').value;
            if (value) {
                yearMinInput.value = value;
                yearMaxInput.value = value;
            } else {
                yearMinInput.value = '';
                yearMaxInput.value = '';
            }
        } else {
            const min = document.getElementById('year_range_min').value;
            const max = document.getElementById('year_range_max').value;
            yearMinInput.value = min || '';
            yearMaxInput.value = max || '';
        }
        updateYearDisplay();
        closeModal('yearModal');
        
        if (window.updateClearButton) {
            window.updateClearButton();
        }
        
        const filtrosForm = document.getElementById('filtrosForm');
        if (filtrosForm) {
            setTimeout(function() {
                filtrosForm.submit();
            }, 350);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (ratingModal.classList.contains('active')) {
                closeModal('ratingModal');
            }
            if (yearModal.classList.contains('active')) {
                closeModal('yearModal');
            }
        }
    });

    updateRatingDisplay();
    updateYearDisplay();
})();

