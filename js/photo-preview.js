/**
 * Sistema Criança Feliz - Componente Universal de Pré-visualização de Imagens
 * Suporta Foto 3x4, Carimbo/Assinatura, Foto de Perfil e Anexos.
 */
(function (window, document) {
    'use strict';

    /**
     * Configura um campo de upload com preview completo (frame, placeholder, badge, status, botão desfazer)
     */
    function setupPhotoPreview(config) {
        const input = document.getElementById(config.inputId);
        const frame = document.getElementById(config.frameId);
        const img = document.getElementById(config.imgId);
        const placeholder = document.getElementById(config.placeholderId);
        const badge = document.getElementById(config.badgeId);
        const status = document.getElementById(config.statusId);
        const resetBtn = document.getElementById(config.resetBtnId);
        const btnText = document.getElementById(config.btnTextId);

        if (!input || !img) return;

        // Guarda estado inicial (útil para edição)
        const initialSrc = img.getAttribute('src') || '';
        const hasInitialImage = Boolean(initialSrc && !initialSrc.endsWith('#') && !img.classList.contains('d-none'));
        const initialStatusText = status ? status.textContent : '';
        const initialBtnText = btnText ? btnText.textContent : 'Escolher Arquivo';

        function handleFile(file) {
            if (!file) return;

            // Validar formato
            if (!file.type.match(/^image\/(jpeg|png|gif|webp|jpg)$/i)) {
                alert('Formato inválido! Por favor envie uma imagem em formato JPG, PNG, GIF ou WEBP.');
                input.value = '';
                return;
            }

            // Validar tamanho (2MB)
            if (file.size > 2 * 1024 * 1024) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                alert(`O arquivo selecionado tem ${sizeMB}MB, excedendo o limite permitido de 2MB. Por favor, comprima ou selecione uma imagem menor.`);
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                img.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');

                if (badge) {
                    badge.innerHTML = '<i class="fas fa-sparkles"></i> Nova Foto';
                    badge.className = 'cf-photo-badge badge-new';
                    badge.classList.remove('d-none');
                }

                if (status) {
                    const sizeKB = (file.size / 1024).toFixed(0);
                    status.innerHTML = `<i class="fas fa-check"></i> ${file.name} (${sizeKB} KB)`;
                }

                if (btnText) {
                    btnText.textContent = config.changeBtnText || 'Substituir Foto';
                }

                if (resetBtn) {
                    resetBtn.classList.remove('d-none');
                }

                if (frame) {
                    frame.classList.add('has-image');
                }
            };
            reader.readAsDataURL(file);
        }

        input.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            handleFile(file);
        });

        // Suporte para arrastar e soltar (drag & drop) no frame
        if (frame) {
            frame.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                frame.style.borderColor = '#ef7417';
                frame.style.transform = 'scale(1.02)';
            });

            frame.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                frame.style.borderColor = '';
                frame.style.transform = '';
            });

            frame.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                frame.style.borderColor = '';
                frame.style.transform = '';
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    input.files = e.dataTransfer.files;
                    handleFile(e.dataTransfer.files[0]);
                }
            });
        }

        // Botão de Desfazer
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                input.value = '';
                if (hasInitialImage) {
                    img.src = initialSrc;
                    img.classList.remove('d-none');
                    if (placeholder) placeholder.classList.add('d-none');
                    if (badge) {
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + (config.initialBadgeText || 'Foto Atual');
                        badge.className = 'cf-photo-badge badge-current';
                        badge.classList.remove('d-none');
                    }
                    if (status) status.textContent = initialStatusText;
                    if (btnText) btnText.textContent = initialBtnText;
                } else {
                    img.src = '';
                    img.classList.add('d-none');
                    if (placeholder) placeholder.classList.remove('d-none');
                    if (badge) badge.classList.add('d-none');
                    if (status) status.textContent = '';
                    if (btnText) btnText.textContent = initialBtnText;
                    if (frame) frame.classList.remove('has-image');
                }
                resetBtn.classList.add('d-none');
            });
        }
    }

    /**
     * Inicialização automática na carga da página
     */
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Foto 3x4 (Acolhimento Form - Cadastro & Edição)
        if (document.getElementById('fotoInput')) {
            setupPhotoPreview({
                inputId: 'fotoInput',
                frameId: 'fotoFrame',
                imgId: 'fotoPreviewImg',
                placeholderId: 'fotoPlaceholder',
                badgeId: 'fotoBadge',
                statusId: 'fotoStatus',
                resetBtnId: 'fotoResetBtn',
                btnTextId: 'fotoBtnText',
                changeBtnText: 'Alterar foto',
                initialBadgeText: 'Foto Atual'
            });
        }

        // 2. Carimbo/Assinatura (Acolhimento Form - Cadastro & Edição)
        if (document.getElementById('carimboInput')) {
            setupPhotoPreview({
                inputId: 'carimboInput',
                frameId: 'carimboFrame',
                imgId: 'carimboPreviewImg',
                placeholderId: 'carimboPlaceholder',
                badgeId: 'carimboBadge',
                statusId: 'carimboStatus',
                resetBtnId: 'carimboResetBtn',
                btnTextId: 'carimboBtnText',
                changeBtnText: 'Alterar carimbo',
                initialBadgeText: 'Carimbo Atual'
            });
        }
    });

    // Expor globalmente para uso pontual se necessário
    window.setupPhotoPreview = setupPhotoPreview;

})(window, document);
