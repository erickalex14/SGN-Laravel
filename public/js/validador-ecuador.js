const EcuadorianValidator = {
    validarCedula(val) {
        val = val.trim();
        if (val.length !== 10 || !/^\d+$/.test(val)) return false;
        
        const prov = parseInt(val.substring(0, 2), 10);
        if ((prov < 1 || prov > 24) && prov !== 30) return false;

        return true;
    },

    validarRuc(val) {
        val = val.trim();
        if (val.length !== 13 || !/^\d+$/.test(val)) return false;
        
        // El RUC debe terminar en algo diferente de 000 (generalmente 001, 002, etc.)
        const establishment = val.substring(10, 13);
        if (establishment === '000') return false;

        const prov = parseInt(val.substring(0, 2), 10);
        if ((prov < 1 || prov > 24) && prov !== 30) return false;
        
        return true;
    },

    validarIdentificacion(val) {
        val = val.trim();
        if (val.length === 10) return EcuadorianValidator.validarCedula(val);
        if (val.length === 13) return EcuadorianValidator.validarRuc(val);
        return false;
    },

    validarTelefono(val) {
        val = val.trim();
        return /^(09\d{8}|0[2-7]\d{7})$/.test(val);
    },

    validarEmail(val) {
        val = val.trim();
        if (val === '') return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }
};

function setupDynamicValidation(input, validate, getErrorMsg) {
    if (!input) return;
    
    // Create error message element if not exists
    let errorEl = input.parentNode.querySelector('.error-mensaje');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'error-mensaje';
        // Insert it right after the input
        input.parentNode.appendChild(errorEl);
    }

    let timer = null;

    function doValidation(isBlur = false) {
        const val = input.value;
        const empty = val.trim() === '';
        
        if (empty && !input.required) {
            input.classList.remove('is-invalid');
            errorEl.style.display = 'none';
            return true;
        }

        const isValid = validate(val);
        
        if (isValid) {
            input.classList.remove('is-invalid');
            errorEl.style.display = 'none';
            return true;
        } else {
            const hasLetters = (validate === EcuadorianValidator.validarIdentificacion || validate === EcuadorianValidator.validarTelefono || validate === EcuadorianValidator.validarRuc || validate === EcuadorianValidator.validarCedula) && /[^\d]/.test(val);
            
            if (isBlur || hasLetters || (val.length >= 10 && validate === EcuadorianValidator.validarIdentificacion) || (val.length >= 13 && validate === EcuadorianValidator.validarRuc) || (val.length >= 10 && validate === EcuadorianValidator.validarTelefono)) {
                input.classList.add('is-invalid');
                errorEl.textContent = getErrorMsg(val);
                errorEl.style.display = 'block';
            } else {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    if (input.value === val && !validate(input.value)) {
                        input.classList.add('is-invalid');
                        errorEl.textContent = getErrorMsg(input.value);
                        errorEl.style.display = 'block';
                    }
                }, 850);
            }
            return false;
        }
    }

    input.addEventListener('input', () => {
        if (validate === EcuadorianValidator.validarIdentificacion && input.value.length > 13) {
            input.value = input.value.substring(0, 13);
        } else if (validate === EcuadorianValidator.validarRuc && input.value.length > 13) {
            input.value = input.value.substring(0, 13);
        } else if (validate === EcuadorianValidator.validarCedula && input.value.length > 10) {
            input.value = input.value.substring(0, 10);
        } else if (validate === EcuadorianValidator.validarTelefono && input.value.length > 10) {
            input.value = input.value.substring(0, 10);
        }
        doValidation(false);
    });

    input.addEventListener('blur', () => {
        clearTimeout(timer);
        doValidation(true);
    });
}
