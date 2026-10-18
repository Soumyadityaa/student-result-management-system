/**
 * script.js
 * Handles Dark/Light theme persistence, client-side validation,
 * dynamic admin record editing, and report printing.
 */

(function () {
    'use strict';

    const THEME_STORAGE_KEY = 'eduresult_theme_preference';
    const rootElement = document.documentElement;

    /**
     * 1. Initialize Theme from localStorage or System Preference
     */
    function initTheme() {
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        if (savedTheme === 'dark' || savedTheme === 'light') {
            rootElement.setAttribute('data-theme', savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            rootElement.setAttribute('data-theme', 'dark');
        } else {
            rootElement.setAttribute('data-theme', 'light');
        }
    }

    // Run immediately to prevent flash of unstyled theme
    initTheme();

    document.addEventListener('DOMContentLoaded', () => {
        /**
         * 2. Theme Toggle Button Handler
         */
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = rootElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                rootElement.setAttribute('data-theme', nextTheme);
                localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
            });
        }

        /**
         * 3. Public Search Form Client-Side Validation (index.php)
         */
        const searchForm = document.getElementById('search-form');
        const rollInput = document.getElementById('roll_number');
        const searchError = document.getElementById('search-error');

        if (searchForm && rollInput && searchError) {
            searchForm.addEventListener('submit', (event) => {
                const rollValue = rollInput.value.trim();
                if (!rollValue) {
                    event.preventDefault();
                    searchError.textContent = 'Please enter your Roll Number before searching.';
                    searchError.hidden = false;
                    rollInput.focus();
                } else {
                    searchError.hidden = true;
                }
            });

            rollInput.addEventListener('input', () => {
                if (rollInput.value.trim() !== '') {
                    searchError.hidden = true;
                }
            });
        }

        /**
         * 4. Print Report Card Button (index.php)
         */
        const printBtn = document.getElementById('print-btn');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                window.print();
            });
        }

        /**
         * 5. Admin Login Form Client-Side Validation (admin_login.php)
         */
        const loginForm = document.getElementById('login-form');
        const loginError = document.getElementById('login-error');

        if (loginForm && loginError) {
            loginForm.addEventListener('submit', (event) => {
                const username = document.getElementById('username')?.value.trim() || '';
                const password = document.getElementById('password')?.value || '';

                if (!username || !password) {
                    event.preventDefault();
                    loginError.textContent = 'Both username and password are required.';
                    loginError.hidden = false;
                } else {
                    loginError.hidden = true;
                }
            });
        }

        /**
         * 6. Admin Mark Entry Form Validation & Dynamic Edit Population (admin_dashboard.php)
         */
        const markEntryForm = document.getElementById('mark-entry-form');
        const markFormError = document.getElementById('mark-form-error');

        if (markEntryForm && markFormError) {
            markEntryForm.addEventListener('submit', (event) => {
                const roll = document.getElementById('roll_number').value.trim();
                const name = document.getElementById('student_name').value.trim();
                const studentClass = document.getElementById('class').value.trim();
                const subjectIds = ['maths', 'science', 'english', 'computer'];

                if (!roll || !name || !studentClass) {
                    event.preventDefault();
                    markFormError.textContent = 'Please fill out Roll Number, Student Full Name, and Class.';
                    markFormError.hidden = false;
                    return;
                }

                for (const id of subjectIds) {
                    const inputEl = document.getElementById(id);
                    const rawVal = inputEl.value.trim();
                    const numVal = Number(rawVal);

                    if (rawVal === '' || !Number.isInteger(numVal) || numVal < 0 || numVal > 100) {
                        event.preventDefault();
                        markFormError.textContent = 'All subject marks must be whole numbers between 0 and 100.';
                        markFormError.hidden = false;
                        inputEl.focus();
                        return;
                    }
                }

                markFormError.hidden = true;
            });

            // Instant form population when clicking "Edit" on any student row
            const editButtons = document.querySelectorAll('.btn-edit[data-student]');
            const formTitle = document.getElementById('form-title');
            const submitBtnText = document.getElementById('submit-btn-text');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');

            editButtons.forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    try {
                        const data = JSON.parse(btn.getAttribute('data-student'));
                        document.getElementById('student_id').value = data.id;
                        document.getElementById('roll_number').value = data.roll_number;
                        document.getElementById('student_name').value = data.student_name;
                        document.getElementById('class').value = data.class;
                        document.getElementById('exam_term').value = data.exam_term;
                        document.getElementById('maths').value = data.maths;
                        document.getElementById('science').value = data.science;
                        document.getElementById('english').value = data.english;
                        document.getElementById('computer').value = data.computer;

                        if (formTitle) formTitle.textContent = 'Update Student Result';
                        if (submitBtnText) submitBtnText.textContent = 'Update Record';
                        if (cancelEditBtn) cancelEditBtn.classList.remove('hidden');
                        markFormError.hidden = true;

                        document.getElementById('mark-entry-panel')?.scrollIntoView({ behavior: 'smooth' });
                    } catch (err) {
                        window.location.href = btn.getAttribute('href');
                    }
                });
            });

            // Confirm before deleting a student record
            const deleteForms = document.querySelectorAll('.inline-delete-form');
            deleteForms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const confirmed = window.confirm('Are you sure you want to permanently delete this student result record?');
                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            });
        }
    });
})();