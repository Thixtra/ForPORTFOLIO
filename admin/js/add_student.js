document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            menu.classList.toggle('hidden');
            this.setAttribute('aria-expanded', !isExpanded);
        });
    }

    // File Upload
    const fileUpload = document.querySelector('.file-upload');
    const csvUpload = document.getElementById('csvUpload');
    const fileName = document.getElementById('fileName');

    if (fileUpload && csvUpload) {
        fileUpload.addEventListener('click', function() {
            csvUpload.click();
        });

        csvUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileName.textContent = `ไฟล์ที่เลือก: ${e.target.files[0].name}`;
                fileUpload.classList.add('border-blue-500', 'bg-blue-50');
            }
        });

        // Drag and Drop for desktop
        if (window.matchMedia("(hover: hover)").matches) {
            fileUpload.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-blue-500', 'bg-blue-50');
            });

            fileUpload.addEventListener('dragleave', function() {
                this.classList.remove('border-blue-500', 'bg-blue-50');
            });

            fileUpload.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-blue-500', 'bg-blue-50');
                
                if (e.dataTransfer.files.length > 0) {
                    csvUpload.files = e.dataTransfer.files;
                    fileName.textContent = `ไฟล์ที่เลือก: ${e.dataTransfer.files[0].name}`;
                }
            });
        }
    }

    // Add new row
    const addRowButton = document.getElementById('add-row');
    if (addRowButton) {
        addRowButton.addEventListener('click', function() {
            const tableBody = document.getElementById('student-rows');
            if (tableBody) {
                const firstRow = tableBody.querySelector('.student-row');
                if (firstRow) {
                    const newRow = firstRow.cloneNode(true);
                    
                    // Clear all inputs in the new row
                    newRow.querySelectorAll('input').forEach(input => {
                        input.value = '';
                        // Trigger placeholder update
                        const event = new Event('input', { bubbles: true });
                        input.dispatchEvent(event);
                    });
                    
                    tableBody.appendChild(newRow);
                    
                    // Scroll to the new row on mobile
                    if (window.innerWidth <= 768) {
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            }
        });
    }

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
            const row = e.target.closest('.student-row');
            if (row) {
                if (document.querySelectorAll('.student-row').length > 1) {
                    row.remove();
                } else {
                    alert('ต้องมีอย่างน้อย 1 แถว');
                }
            }
        }
    });

    // Clear all inputs
    const clearAllButton = document.getElementById('clear-all');
    if (clearAllButton) {
        clearAllButton.addEventListener('click', function() {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการล้างข้อมูลทั้งหมด?')) {
                document.querySelectorAll('.student-row input').forEach(input => {
                    input.value = '';
                    // Trigger placeholder update
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }
        });
    }

    // Floating label functionality
    function setupFloatingLabels() {
        document.querySelectorAll('input').forEach(input => {
            // Initialize based on existing values
            const label = input.nextElementSibling;
            if (input.value.trim() !== '' && label && label.classList.contains('absolute')) {
                label.classList.add('text-xs', '-top-2', 'bg-white', 'px-1', 'text-blue-600');
            }
            
            // Add event listeners
            input.addEventListener('input', function() {
                const label = this.nextElementSibling;
                if (label && label.classList.contains('absolute')) {
                    if (this.value.trim() !== '') {
                        label.classList.add('text-xs', '-top-2', 'bg-white', 'px-1', 'text-blue-600');
                    } else {
                        label.classList.remove('text-xs', '-top-2', 'bg-white', 'px-1', 'text-blue-600');
                    }
                }
            });
        });
    }

    // Initialize floating labels
    setupFloatingLabels();
    
    // Better form submission handling
    const multiStudentForm = document.getElementById('multi-student-form');
    if (multiStudentForm) {
        multiStudentForm.addEventListener('submit', function(e) {
            // Validate all inputs before submission
            let isValid = true;
            const inputs = this.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.focus();
                    input.classList.add('border-red-500');
                    
                    // Add error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-red-500 text-sm mt-1';
                    errorDiv.textContent = 'กรุณากรอกข้อมูลนี้';
                    
                    // Remove any existing error message
                    const existingError = input.nextElementSibling?.nextElementSibling;
                    if (existingError && existingError.className.includes('text-red-500')) {
                        existingError.remove();
                    }
                    
                    input.parentNode.insertBefore(errorDiv, input.nextElementSibling?.nextElementSibling || input.nextElementSibling);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Better number input handling for mobile
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('keydown', function(e) {
            // Prevent non-numeric input
            if (['e', 'E', '+', '-'].includes(e.key)) {
                e.preventDefault();
            }
        });
        
        // Better mobile keyboard
        if ('ontouchstart' in window) {
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('pattern', '[0-9]*');
        }
    });
    
    // Better citizen ID input handling
    document.querySelectorAll('input[name="student_citizen_id[]"]').forEach(input => {
        input.addEventListener('input', function() {
            // Auto-format for better UX
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Validate length
            if (this.value.length > 13) {
                this.value = this.value.slice(0, 13);
            }
        });
    });
}); 