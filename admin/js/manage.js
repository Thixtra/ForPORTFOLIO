// ป้องกันการโหลดซ้ำเมื่อฟอร์มถูกส่ง
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });
    
    document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });
    
    // Mobile Menu Toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        this.querySelector('i').classList.toggle('fa-bars');
        this.querySelector('i').classList.toggle('fa-times');
    });
    
    // ปิด Modal เมื่อคลิกนอกพื้นที่
    document.getElementById('studentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStudentModal();
        }
    });
    
    document.getElementById('teacherModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTeacherModal();
        }
    });
    
    // ฟังก์ชันการค้นหานักเรียน
    document.getElementById('studentSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.student-row');
        let hasVisibleRows = false;
        
        // ถ้ามีการค้นหา ให้ล้างค่า pagination
        if (searchTerm !== '') {
            const url = new URL(window.location);
            url.searchParams.delete('student_page');
            window.history.replaceState({}, '', url);
        }
        
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // แสดงข้อความเมื่อไม่พบผลลัพธ์
        const tbody = document.getElementById('studentTableBody');
        let noResultsRow = document.getElementById('noStudentResults');
        
        if (!hasVisibleRows && searchTerm !== '') {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noStudentResults';
                noResultsRow.innerHTML = '<td colspan="6" class="py-8 px-4 text-center text-gray-500"><i class="fas fa-search mr-2"></i>ไม่พบข้อมูลที่ค้นหา</td>';
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    });
    
    // ฟังก์ชันการค้นหาอาจารย์
    document.getElementById('teacherSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.teacher-row');
        let hasVisibleRows = false;
        
        // ถ้ามีการค้นหา ให้ล้างค่า pagination
        if (searchTerm !== '') {
            const url = new URL(window.location);
            url.searchParams.delete('teacher_page');
            window.history.replaceState({}, '', url);
        }
        
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // แสดงข้อความเมื่อไม่พบผลลัพธ์
        const tbody = document.getElementById('teacherTableBody');
        let noResultsRow = document.getElementById('noTeacherResults');
        
        if (!hasVisibleRows && searchTerm !== '') {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noTeacherResults';
                noResultsRow.innerHTML = '<td colspan="6" class="py-8 px-4 text-center text-gray-500"><i class="fas fa-search mr-2"></i>ไม่พบข้อมูลที่ค้นหา</td>';
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    });
    
    // ปรับปรุงการแสดงผลเมื่อหน้าจอเปลี่ยนขนาด
    window.addEventListener('resize', function() {
        // ปรับขนาด Modal ให้เหมาะสม
        const studentModal = document.getElementById('studentModal');
        const teacherModal = document.getElementById('teacherModal');
        
        if (window.innerWidth < 640) {
            if (studentModal.classList.contains('flex')) {
                studentModal.classList.add('p-2');
            }
            if (teacherModal.classList.contains('flex')) {
                teacherModal.classList.add('p-2');
            }
        } else {
            studentModal.classList.remove('p-2');
            teacherModal.classList.remove('p-2');
        }
    });
    
    // เพิ่ม event listener สำหรับปุ่ม pagination เพื่อล้างการค้นหา
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="student_page"]')) {
            document.getElementById('studentSearch').value = '';
            // ล้างการซ่อนแถวที่เกิดจากการค้นหา
            document.querySelectorAll('.student-row').forEach(row => {
                row.style.display = '';
            });
            // ลบข้อความ "ไม่พบข้อมูล"
            const noResultsRow = document.getElementById('noStudentResults');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
        
        if (e.target.closest('a[href*="teacher_page"]')) {
            document.getElementById('teacherSearch').value = '';
            // ล้างการซ่อนแถวที่เกิดจากการค้นหา
            document.querySelectorAll('.teacher-row').forEach(row => {
                row.style.display = '';
            });
            // ลบข้อความ "ไม่พบข้อมูล"
            const noResultsRow = document.getElementById('noTeacherResults');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    });
});

// ฟังก์ชันแก้ไขข้อมูลนักเรียน
function editStudent(student) {
    document.getElementById('edit_student_id').value = student.id;
    document.getElementById('edit_student_year').value = student.year;
    document.getElementById('edit_student_title').value = student.title;
    document.getElementById('edit_student_name').value = student.name;
    document.getElementById('edit_student_surname').value = student.surname;
    document.getElementById('edit_student_number').value = student.number;
    document.getElementById('edit_student_classroom').value = student.classroom;
    
    const modal = document.getElementById('studentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// ฟังก์ชันปิด Modal นักเรียน
function closeStudentModal() {
    const modal = document.getElementById('studentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

// ฟังก์ชันแก้ไขข้อมูลอาจารย์
function editTeacher(teacher) {
    document.getElementById('edit_teacher_id').value = teacher.id;
    document.getElementById('edit_teacher_year').value = teacher.year;
    document.getElementById('edit_teacher_title').value = teacher.title;
    document.getElementById('edit_teacher_name').value = teacher.name;
    document.getElementById('edit_teacher_surname').value = teacher.surname;
    document.getElementById('edit_teacher_classroom').value = teacher.classroom;
    document.getElementById('edit_teacher_subject_group').value = teacher.subject_group;
    
    const modal = document.getElementById('teacherModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// ฟังก์ชันปิด Modal อาจารย์
function closeTeacherModal() {
    const modal = document.getElementById('teacherModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}