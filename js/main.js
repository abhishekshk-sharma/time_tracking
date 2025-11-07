


 



    $(document).ready(function(){

        // Open modals
        function openEmailModal() {
            $('#emailModal').show();
        }

        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex';
        }

        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Update email with SweetAlert
        function updateEmail() {
            const newEmail = document.getElementById('newEmail').value;
            const confirmEmail = document.getElementById('confirmEmail').value;
            
            if (!newEmail || !confirmEmail) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all fields!',
                });
                return;
            }
            
            if (newEmail !== confirmEmail) {
                Swal.fire({
                    icon: 'error',
                    title: 'Emails do not match',
                    text: 'Please make sure both email addresses are identical.',
                });
                return;
            }
            
            // Simulate API call
            Swal.fire({
                title: 'Updating Email',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Updated',
                    text: 'Your email address has been successfully updated.',
                }).then(() => {
                    closeModal('emailModal');
                    // In a real app, you would update the UI with the new email
                    document.querySelector('.detail-card:nth-child(1) p').textContent = newEmail;
                });
            }, 1500);
        }

        // Update password with SweetAlert
        function updatePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all fields!',
                });
                return;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords do not match',
                    text: 'Please make sure both passwords are identical.',
                });
                return;
            }
            
            if (newPassword.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password too short',
                    text: 'Password must be at least 6 characters long.',
                });
                return;
            }
            
            // Simulate API call
            Swal.fire({
                title: 'Updating Password',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated',
                    text: 'Your password has been successfully updated.',
                }).then(() => {
                    closeModal('passwordModal');
                    // Clear password fields
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                });
            }, 1500);
        }
    
    });

 
 
 
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeElement = document.querySelector('.current-time');
            const dateElement = document.querySelector('.current-date');
  
            
            const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('en-US', dateOptions);
            
            timeElement.textContent = timeString;
            dateElement.textContent = dateString;
        }
        
        // Initial time update
        updateTime();
        
        // Update time every second
        setInterval(updateTime, 1000);
        
        // Navigation between employee and admin views
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const target = this.getAttribute('data-target');
                if (!target) return;
                
                // Hide all sections
                document.querySelectorAll('#employee-section, #admin-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show target section
                document.getElementById(target).style.display = 'block';
                
                // Update active nav item
                document.querySelectorAll('.nav-item').forEach(navItem => {
                    navItem.classList.remove('active');
                });
                
                this.classList.add('active');
            });
        }); 
        
        // Punch action handlers
        document.getElementById('punch-in').addEventListener('click', function() {
            // alert('Punched In successfully!');
            // this.disabled = true;
            // document.getElementById('lunch-start').disabled = false;
            // document.getElementById('punch-out').disabled = false;
            
            // Add to activity list
            // addActivity('Punch In');
        });
        
        document.getElementById('lunch-start').addEventListener('click', function() {
            // alert('Lunch started successfully!' );
            // this.disabled = true;
            // document.getElementById('lunch-end').disabled = false;
            
            // Add to activity list
            // addActivity('Lunch Start');
        });
        
        document.getElementById('lunch-end').addEventListener('click', function() {
            // alert('Lunch ended successfully!');
            // this.disabled = true;
            // document.getElementById('lunch-start').disabled = false;
            
            // Add to activity list
            // addActivity('Lunch End');
        });
        
        document.getElementById('punch-out').addEventListener('click', function() {
            // alert('Punched Out successfully!');
            // this.disabled = true;
            // document.getElementById('punch-in').disabled = false;
            // document.getElementById('lunch-start').disabled = true;
            // document.getElementById('lunch-end').disabled = true;
            
            // Add to activity list
            // addActivity('Punch Out');
        });
        
        // Add activity to the list
        function addActivity(action) {
            const activityList = document.querySelector('.activity-list');
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {hour12: false});
            
            const icons = {
                'Punch In': 'fa-fingerprint',
                'Punch Out': 'fa-door-open',
                'Lunch Start': 'fa-utensils',
                'Lunch End': 'fa-utensils'
            };
            
            const li = document.createElement('li');
            li.className = 'activity-item';
            li.innerHTML = `
                <div class="activity-icon">
                    <i class="fas ${icons[action]}"></i>
                </div>
                <div class="activity-details">
                    <div class="activity-name">${action}</div>
                    <div class="activity-time">${timeString}</div>
                </div>
            `;
            
            activityList.prepend(li);
        }
        


