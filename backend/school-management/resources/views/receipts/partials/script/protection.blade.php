<script>
    (function() {
        'use strict';

        const ORIGINAL_STATE = {
            id: '{{ $receipt->id ?? '' }}',
            folio: '{{ $receipt->folio ?? '' }}',
            amount: '{{ $receipt->amount ?? '' }}',
            received: '{{ $receipt->amount_received ?? '' }}',
            concept: '{{ $receipt->concept_name ?? '' }}',
            reference: '{{ $receipt->transaction_reference ?? '' }}',
            payerName: '{{ $receipt->payer_name ?? '' }}',
            payerEmail: '{{ $receipt->payer_email ?? '' }}',
            date: '{{ $receipt->metadata['payment_date'] ?? '' }}',
            issuedAt: '{{ $receipt->issued_at ?? '' }}',
            hash: '{{ hash('sha256', ($receipt->id ?? '') . ($receipt->folio ?? '') . ($receipt->amount ?? '')) }}'
        };

        function verifyAllElements() {
            try {
                const folioElement = document.querySelector('.folio-value');
                if (!folioElement) {
                    handleTampering('Elemento folio eliminado');
                    return false;
                }

                const currentFolio = folioElement.textContent.trim();
                if (currentFolio !== ORIGINAL_STATE.folio) {
                    handleTampering('Folio modificado');
                    return false;
                }

                const nameElement = document.querySelector('.student-name');
                if (!nameElement) {
                    handleTampering('Elemento nombre eliminado');
                    return false;
                }

                const currentName = nameElement.textContent.trim();
                if (currentName !== ORIGINAL_STATE.payerName) {
                    handleTampering('Nombre del alumno modificado');
                    return false;
                }

                const emailElement = document.querySelector('.student-email');
                if (emailElement) {
                    const currentEmail = emailElement.textContent.trim().replace(/\s+/g, '');
                    const originalEmail = ORIGINAL_STATE.payerEmail.replace(/\s+/g, '');
                    if (currentEmail !== originalEmail) {
                        handleTampering('Email modificado');
                        return false;
                    }
                }

                const amountElement = document.querySelector('.amount-value');
                if (!amountElement) {
                    handleTampering('Elemento monto eliminado');
                    return false;
                }

                const currentAmount = amountElement.textContent.trim();
                const cleanCurrentAmount = currentAmount.replace(/[^0-9.-]+/g, '');
                const cleanOriginalAmount = ORIGINAL_STATE.received.toString().replace(/[^0-9.-]+/g, '');

                if (cleanCurrentAmount !== cleanOriginalAmount) {
                    handleTampering('Monto modificado');
                    return false;
                }

                const conceptElements = document.querySelectorAll('.detail-value.strong');
                let conceptFound = false;

                conceptElements.forEach(element => {
                    if (element.textContent.includes(ORIGINAL_STATE.concept.substring(0, 15))) {
                        conceptFound = true;
                    }
                });

                if (!conceptFound) {
                    const allStrongElements = document.querySelectorAll('.detail-value');
                    allStrongElements.forEach(element => {
                        if (element.textContent.includes(ORIGINAL_STATE.concept.substring(0, 15))) {
                            conceptFound = true;
                        }
                    });
                }

                if (!conceptFound && ORIGINAL_STATE.concept) {
                    handleTampering('Concepto modificado');
                    return false;
                }

                if (ORIGINAL_STATE.reference) {
                    const referenceElement = document.querySelector('.reference-value');
                    if (referenceElement) {
                        const currentReference = referenceElement.textContent.trim();
                        if (currentReference !== ORIGINAL_STATE.reference) {
                            handleTampering('Referencia modificada');
                            return false;
                        }
                    }
                }

                const dateElements = document.querySelectorAll('.detail-value');
                let dateFound = false;

                if (ORIGINAL_STATE.issuedAt) {
                    const originalDate = new Date(ORIGINAL_STATE.issuedAt).toLocaleDateString('es-MX');

                    dateElements.forEach(element => {
                        const text = element.textContent;
                        if (text.includes(originalDate) ||
                            (ORIGINAL_STATE.date && text.includes(ORIGINAL_STATE.date.substring(0, 10)))) {
                            dateFound = true;
                        }
                    });

                    if (!dateFound) {
                        handleTampering('Fecha modificada');
                        return false;
                    }
                }

                return true;

            } catch (error) {
                handleTampering('Error en verificación');
                return false;
            }
        }

        const observer = new MutationObserver(function(mutations) {
            let significantChange = false;
            let modifiedElements = [];

            mutations.forEach(function(mutation) {
                if (mutation.removedNodes.length > 0) {
                    Array.from(mutation.removedNodes).forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            const html = node.outerHTML || '';
                            if (html.includes('folio') ||
                                html.includes('amount') ||
                                html.includes('student-name') ||
                                html.includes('reference')) {
                                significantChange = true;
                                modifiedElements.push('elemento eliminado');
                            }
                        }
                    });
                }

                if (mutation.type === 'attributes') {
                    const target = mutation.target;
                    if (target.classList &&
                        (target.classList.contains('folio-value') ||
                            target.classList.contains('amount-value') ||
                            target.classList.contains('student-name'))) {
                        significantChange = true;
                        modifiedElements.push('atributo modificado');
                    }
                }

                if (mutation.type === 'characterData') {
                    const parent = mutation.target.parentElement;
                    if (parent &&
                        (parent.classList.contains('folio-value') ||
                            parent.classList.contains('amount-value') ||
                            parent.classList.contains('student-name'))) {
                        significantChange = true;
                        modifiedElements.push('texto modificado');
                    }
                }
            });

            if (significantChange) {
                handleTampering('Cambio detectado: ' + modifiedElements.join(', '));
            } else {
                verifyAllElements();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style'],
            characterData: true
        });

        setInterval(function() {
            verifyAllElements();
            detectDevTools();
            detectDevToolsBySize();
        }, 2000);


        function detectDevTools() {
            const start = performance.now();
            debugger;
            const end = performance.now();

            if (end - start > 100) {
                handleTampering('DevTools detectado');
                return true;
            }
            return false;
        }

        function detectDevToolsBySize() {
            const threshold = 200;
            const widthDiff = window.outerWidth - window.innerWidth;
            const heightDiff = window.outerHeight - window.innerHeight;

            if (widthDiff > threshold || heightDiff > threshold) {
                handleTampering('DevTools detectado');
                return true;
            }
            return false;
        }

        function handleTampering(reason) {
            showAlert(
                'DOCUMENTO NO VÁLIDO',
                'Este recibo ha sido modificado. Se recargará la versión original.'
            );

            setTimeout(function() {
                location.reload();
            }, 3000);
        }

        function showWarning(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #013237;
                color: white;
                padding: 10px 20px;
                border-radius: 30px;
                font-size: 14px;
                z-index: 10000;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                animation: slideDown 0.3s ease;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }

        function showAlert(title, message) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10001;
            `;

            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 16px;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    border-left: 6px solid #dc3545;
                ">
                    <h3 style="margin-top: 0; color: #013237;">${title}</h3>
                    <p style="color: #666;">${message}</p>
                    <p style="color: #999; font-size: 12px;">Recargando en 3 segundos...</p>
                </div>
            `;

            document.body.appendChild(modal);
        }


        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showWarning('Clic derecho deshabilitado');
            return false;
        });

        document.addEventListener('keydown', function(e) {
            const blockedKeys = ['F12', 'u', 's', 'r', 'I', 'J', 'C'];
            if (e.key === 'F12' ||
                (e.ctrlKey && e.shiftKey && blockedKeys.includes(e.key)) ||
                (e.ctrlKey && blockedKeys.includes(e.key.toLowerCase()))) {
                e.preventDefault();
                showWarning('Acción no permitida');
                return false;
            }
        });

        document.addEventListener('copy', function(e) {
            e.preventDefault();
            showWarning('Copia deshabilitada');
            return false;
        });

        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });


        window.addEventListener('load', function() {
            verifyAllElements();
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translate(-50%, -20px);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, 0);
                }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);

    })();
</script>
