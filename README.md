# wordpress-plugin
Custom Wordpress booking Plugin

Silver Score Questions Filter Plugin
Description

The Silver Score Questions Filter Plugin is a custom WordPress plugin developed to display a list of questions (with their creation dates) and filter them based on a selected date. This plugin is useful for managing questions submitted through the platform and allows easy navigation of content based on a specific date.

Features

✅ Display Questions with Date: The plugin fetches questions from the database and displays them with their respective created date.
✅ Date Filtering Option: Users can filter questions based on a specific date using a date picker input.
✅ Custom Calendar Icon: The date input field is enhanced with a custom calendar icon for better UI/UX.
✅ Clean and Minimal Design: The plugin form is designed with a clean, modern layout using custom CSS.
✅ Responsive Design: The form and question table are responsive and adapt to different screen sizes.

Installation

Upload Plugin: Upload the plugin folder (silverscore) to the /wp-content/plugins/ directory.
Activate Plugin: Go to WordPress Dashboard > Plugins and activate the plugin.

silver-score/
│── silverscore.php
│── includes/
│   ├── admin-questions.php
│   ├── admin-users.php
│   ├── db-setup.php
│   ├── quiz-display.php
│   ├── user-dashboard.php
│   ├── user-registration.php
│── assets/
│   ├── style.css
│   ├── script.js
│── uninstall.php
│── readme.txt


Usage

Filter by Date:

Select a specific date from the date picker to filter questions.
Click Filter to retrieve questions created on that date.

View Questions:

The table displays the question and its creation date in Y-m-d format.
If no questions exist for the selected date, a No questions found message will appear.

Custom Styling

The plugin offers a visually appealing form with a:
Custom calendar icon 📅 for date selection.
Responsive button with hover effects.
Clean table layout for question display.

License

This plugin is developed as a custom solution and is licensed to the client only. Unauthorized distribution, modification, or replication without permission is prohibited.

Support

For any customization, bug fixes, or enhancements, please contact:
💻 Viewebit - https://viewebit.com
📧 Email: darshit@viewebit.com
