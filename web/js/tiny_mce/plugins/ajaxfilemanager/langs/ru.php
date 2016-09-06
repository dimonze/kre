<?php
	/**
	 * language pack
	 * @author Garin Studio
	 * @link www.garin-studio.ru
	 * @since 25/May/2011
	 *
	 */
	define('DATE_TIME_FORMAT', 'd M Y H:i');
	//Common
	//Menu
	
	
	
	
	define('MENU_SELECT', 'Выбрать');
	define('MENU_DOWNLOAD', 'Скачать');
	define('MENU_PREVIEW', 'Посмотреть');
	define('MENU_RENAME', 'Переименовать');
	define('MENU_EDIT', 'Изменить');
	define('MENU_CUT', 'Вырезать');
	define('MENU_COPY', 'Копировать');
	define('MENU_DELETE', 'Удалить');
	define('MENU_PLAY', 'Воспроизвести');
	define('MENU_PASTE', 'Вставить');
	
	//Label
		//Top Action
		define('LBL_ACTION_REFRESH', 'Обновить');
		define('LBL_ACTION_DELETE', 'Удалить');
		define('LBL_ACTION_CUT', 'Вырезать');
		define('LBL_ACTION_COPY', 'Копировать');
		define('LBL_ACTION_PASTE', 'Вставить');
		define('LBL_ACTION_CLOSE', 'Закрыть');
		define('LBL_ACTION_SELECT_ALL', 'Выбрать все');
		//File Listing
	define('LBL_NAME', 'Имя');
	define('LBL_SIZE', 'Размер');
	define('LBL_MODIFIED', 'Дата изменения');
		//File Information
	define('LBL_FILE_INFO', 'Описание файла:');
	define('LBL_FILE_NAME', 'Има:');	
	define('LBL_FILE_CREATED', 'Создан:');
	define('LBL_FILE_MODIFIED', 'Изменен:');
	define('LBL_FILE_SIZE', 'Размер:');
	define('LBL_FILE_TYPE', 'Тип:');
	define('LBL_FILE_WRITABLE', 'Запись?');
	define('LBL_FILE_READABLE', 'Чтение?');
		//Folder Information
	define('LBL_FOLDER_INFO', 'Описание папки');
	define('LBL_FOLDER_PATH', 'Имя:');
	define('LBL_CURRENT_FOLDER_PATH', 'Текущая папка:');
	define('LBL_FOLDER_CREATED', 'Создана:');
	define('LBL_FOLDER_MODIFIED', 'Изменена:');
	define('LBL_FOLDER_SUDDIR', 'Подпапок:');
	define('LBL_FOLDER_FIELS', 'Файлов:');
	define('LBL_FOLDER_WRITABLE', 'Запись?');
	define('LBL_FOLDER_READABLE', 'Чтение?');
	define('LBL_FOLDER_ROOT', 'Корневая папка');
		//Preview
	define('LBL_CLICK_PREVIEW', 'Нажмите чтобы посмотреть.');
	//Buttons
	define('LBL_BTN_SELECT', 'Выбрать');
	define('LBL_BTN_CANCEL', 'Отменить');
	define('LBL_BTN_UPLOAD', 'Закачать');
	define('LBL_BTN_CREATE', 'Создать');
	define('LBL_BTN_CLOSE', 'Закрыть');
	define('LBL_BTN_NEW_FOLDER', 'Создать папку');
	define('LBL_BTN_NEW_FILE', 'Создать файл');
	define('LBL_BTN_EDIT_IMAGE', 'Изменить');
	define('LBL_BTN_VIEW_DETAILS', 'Таблица');
	define('LBL_BTN_VIEW_THUMBNAIL', 'Значки');
	define('LBL_BTN_VIEW_OPTIONS', 'Вид:');
	//pagination
	define('PAGINATION_NEXT', 'След.');
	define('PAGINATION_PREVIOUS', 'Пред.');
	define('PAGINATION_LAST', 'Посл.');
	define('PAGINATION_FIRST', 'Перв.');
	define('PAGINATION_ITEMS_PER_PAGE', '%s элементов на странице');
	define('PAGINATION_GO_PARENT', 'Go Parent Folder');
	//System
	define('SYS_DISABLED', 'Доступ запрещен: система недоступна.');
	
	//Cut
	define('ERR_NOT_DOC_SELECTED_FOR_CUT', 'Не выбрано объектов чтобы вырезать.');
	//Copy
	define('ERR_NOT_DOC_SELECTED_FOR_COPY', 'Не выбрано объектов для копирования.');
	//Paste
	define('ERR_NOT_DOC_SELECTED_FOR_PASTE', 'Отсутствуют документы для вставки.');
	define('WARNING_CUT_PASTE', 'Вы действительно хотите переместить выбранные объекты в текущую папку?');
	define('WARNING_COPY_PASTE', 'Вы действительно хотите скопировать выбранные объекты в текущую папку?');
	define('ERR_NOT_DEST_FOLDER_SPECIFIED', 'Папка назначения не выбрана.');
	define('ERR_DEST_FOLDER_NOT_FOUND', 'Папка назначения не найдена.');
	define('ERR_DEST_FOLDER_NOT_ALLOWED', 'Недопустимая папка.');
	define('ERR_UNABLE_TO_MOVE_TO_SAME_DEST', 'Ошибка перемещения файла (%s): место назначения не отличается от места текущего положения.');
	define('ERR_UNABLE_TO_MOVE_NOT_FOUND', 'Ошибка перемещениф файла (%s): файл не найден.');
	define('ERR_UNABLE_TO_MOVE_NOT_ALLOWED', 'Ошибка перемещениф файла (%s): доступ к файлу запрещен.');
 
	define('ERR_NOT_FILES_PASTED', 'Ничего не было вставлено.');

	//Search
	define('LBL_SEARCH', 'Поиск');
	define('LBL_SEARCH_NAME', 'Имя файла:');
	define('LBL_SEARCH_FOLDER', 'Искать в:');
	define('LBL_SEARCH_QUICK', 'Быстрый поиск');
	define('LBL_SEARCH_MTIME', 'Время изменения файла (диапазон):');
	define('LBL_SEARCH_SIZE', 'Размер файла:');
	define('LBL_SEARCH_ADV_OPTIONS', 'Расширенные опции');
	define('LBL_SEARCH_FILE_TYPES', 'Типы файлов:');
	define('SEARCH_TYPE_EXE', 'Приложение');
	
	define('SEARCH_TYPE_IMG', 'Изображение');
	define('SEARCH_TYPE_ARCHIVE', 'Архив');
	define('SEARCH_TYPE_HTML', 'HTML');
	define('SEARCH_TYPE_VIDEO', 'Видео');
	define('SEARCH_TYPE_MOVIE', 'Кино');
	define('SEARCH_TYPE_MUSIC', 'Музыка');
	define('SEARCH_TYPE_FLASH', 'Флэш');
	define('SEARCH_TYPE_PPT', 'PowerPoint');
	define('SEARCH_TYPE_DOC', 'Документы');
	define('SEARCH_TYPE_WORD', 'Word');
	define('SEARCH_TYPE_PDF', 'PDF');
	define('SEARCH_TYPE_EXCEL', 'Excel');
	define('SEARCH_TYPE_TEXT', 'Текст');
	define('SEARCH_TYPE_UNKNOWN', 'неизвестно');
	define('SEARCH_TYPE_XML', 'XML');
	define('SEARCH_ALL_FILE_TYPES', 'Все типы файлов');
	define('LBL_SEARCH_RECURSIVELY', 'Рекурсивный поиск:');
	define('LBL_RECURSIVELY_YES', 'Да');
	define('LBL_RECURSIVELY_NO', 'Нет');
	define('BTN_SEARCH', 'Искать');
	//thickbox
	define('THICKBOX_NEXT', 'Вперед&gt;');
	define('THICKBOX_PREVIOUS', '&lt;Назад');
	define('THICKBOX_CLOSE', 'Закрыть');
	//Calendar
	define('CALENDAR_CLOSE', 'Закрыть');
	define('CALENDAR_CLEAR', 'Очистить');
	define('CALENDAR_PREVIOUS', '&lt;Назад');
	define('CALENDAR_NEXT', 'Вперед&gt;');
	define('CALENDAR_CURRENT', 'Сегодня');
	define('CALENDAR_MON', 'Пн.');
	define('CALENDAR_TUE', 'Вт.');
	define('CALENDAR_WED', 'Ср.');
	define('CALENDAR_THU', 'Чт.');
	define('CALENDAR_FRI', 'Пт.');
	define('CALENDAR_SAT', 'Сб.');
	define('CALENDAR_SUN', 'Вс.');
	define('CALENDAR_JAN', 'Янв');
	define('CALENDAR_FEB', 'Фкв');
	define('CALENDAR_MAR', 'Мар');
	define('CALENDAR_APR', 'Апр');
	define('CALENDAR_MAY', 'Май');
	define('CALENDAR_JUN', 'Июн');
	define('CALENDAR_JUL', 'Июл');
	define('CALENDAR_AUG', 'Авг');
	define('CALENDAR_SEP', 'Сен');
	define('CALENDAR_OCT', 'Окт');
	define('CALENDAR_NOV', 'Ноя');
	define('CALENDAR_DEC', 'Дек');
	//ERROR MESSAGES
		//deletion
	define('ERR_NOT_FILE_SELECTED', 'Пожалуйста, выберите файл.');
	define('ERR_NOT_DOC_SELECTED', 'Не выбрано объектов для удаления.');
	define('ERR_DELTED_FAILED', 'Невозможно удалить выбранные объекты.');
	define('ERR_FOLDER_PATH_NOT_ALLOWED', 'Недопустимый путь.');
		//class manager
	define('ERR_FOLDER_NOT_FOUND', 'Не удалось найти указанную папку: ');
		//rename
	define('ERR_RENAME_FORMAT', 'Имя содержит недопустимые символы.');
	define('ERR_RENAME_EXISTS', 'Такое имя уже существует в текущей папке.');
	define('ERR_RENAME_FILE_NOT_EXISTS', 'Объект не существует.');
	define('ERR_RENAME_FAILED', 'Не удалось переименовать. Попробуйте еще раз.');
	define('ERR_RENAME_EMPTY', 'Пожалуйста, введите имя.');
	define('ERR_NO_CHANGES_MADE', 'Изменения отсутствуют.');
	define('ERR_RENAME_FILE_TYPE_NOT_PERMITED', 'Вам запрещено сохранять файлы этого типа.');
		//folder creation
	define('ERR_FOLDER_FORMAT', 'Имя папки содержит недопустимые символы.');
	define('ERR_FOLDER_EXISTS', 'Такое имя уже существует в текущей папке.');
	define('ERR_FOLDER_CREATION_FAILED', 'Не удалось создать папку. Попробуйте еще раз.');
	define('ERR_FOLDER_NAME_EMPTY', 'Пожалуйста, введите имя.');
	define('FOLDER_FORM_TITLE', 'Новая папка');
	define('FOLDER_LBL_TITLE', 'Имя:');
	define('FOLDER_LBL_CREATE', 'Создать папку');
	//New File
	define('NEW_FILE_FORM_TITLE', 'Новый файл');
	define('NEW_FILE_LBL_TITLE', 'Имя:');
	define('NEW_FILE_CREATE', 'Создать файл');
		//file upload
	define('ERR_FILE_NAME_FORMAT', 'Имя файла содержит недопустимые символы.');
	define('ERR_FILE_NOT_UPLOADED', 'Не выбран файл для загрузки.');
	define('ERR_FILE_TYPE_NOT_ALLOWED', 'Вам запрещено закачивать файлы этого типа.');
	define('ERR_FILE_MOVE_FAILED', 'Не удалось переместить файл.');
	define('ERR_FILE_NOT_AVAILABLE', 'Файл недоступен.');
	define('ERROR_FILE_TOO_BID', 'Файл слишком велик. (max: %s)');
	define('FILE_FORM_TITLE', 'Загрузка файла');
	define('FILE_LABEL_SELECT', 'Выберите файл');
	define('FILE_LBL_MORE', 'Add File Uploader');
	define('FILE_CANCEL_UPLOAD', 'Отменить загрузку');
	define('FILE_LBL_UPLOAD', 'Загрузить');
	//file download
	define('ERR_DOWNLOAD_FILE_NOT_FOUND', 'Не выбрано объектов для закачки.');
	//Rename
	define('RENAME_FORM_TITLE', 'Переименование');
	define('RENAME_NEW_NAME', 'Новое имя');
	define('RENAME_LBL_RENAME', 'Переименовать');

	//Tips
	define('TIP_SELECT_ALL', 'Выбрать все');
	define('TIP_UNSELECT_ALL', 'Снять все');
	//WARNING
	define('WARNING_DELETE', 'Вы точно хотите удалить выбранные объекты?');
	define('WARNING_IMAGE_EDIT', 'Пожалуйста, выберите изображение для изменения.');
	define('WARNING_NOT_FILE_EDIT', 'Пожалуйста, выберите файл для изменения.');
	define('WARING_WINDOW_CLOSE', 'Вы точно хотите закрыть окно?');
	//Preview
	define('PREVIEW_NOT_PREVIEW', 'Просмотр недоступен.');
	define('PREVIEW_OPEN_FAILED', 'Не удается открыть файл.');
	define('PREVIEW_IMAGE_LOAD_FAILED', 'Не удается открыть изображение.');

	//Login
	define('LOGIN_PAGE_TITLE', 'Ajax File Manager Login Form');
	define('LOGIN_FORM_TITLE', 'Вход');
	define('LOGIN_USERNAME', 'Логин:');
	define('LOGIN_PASSWORD', 'Пароль:');
	define('LOGIN_FAILED', 'Неправильный логин или пароль.');
	
	
	//88888888888   Below for Image Editor   888888888888888888888
		//Warning 
		define('IMG_WARNING_NO_CHANGE_BEFORE_SAVE', 'Вы не сделали ни каких изменений.');
		
		//General
		define('IMG_GEN_IMG_NOT_EXISTS', 'Изображение недоступно');
		define('IMG_WARNING_LOST_CHANAGES', 'Все несохраненные данные будут утеряны. Вы всё равно хотите продолжить?');
		define('IMG_WARNING_REST', 'Все несохраненные данные будут утеряны. Вы действительно хотите произвести сброс?');
		define('IMG_WARNING_EMPTY_RESET', 'Ни каких изменений изображения сделано не было');
		define('IMG_WARNING_UNDO', 'Вы точно хотите откатить последнее действие?');
		define('IMG_WARING_FLIP_H', 'Вы точно хотите отразить изображение вертикально?');
		define('IMG_WARING_FLIP_V', 'Вы точно хотите отразить изображение горизонтально?');
    define('IMG_WARING_WIN_CLOSE', 'Вы действительно хотите закрыть окно?');
		define('IMG_INFO', 'Детали изображения');
		
		//Mode
			define('IMG_MODE_RESIZE', 'Изм.размер:');
			define('IMG_MODE_CROP', 'Кадрировать:');
			define('IMG_MODE_ROTATE', 'Повернуть:');
			define('IMG_MODE_FLIP', 'Отразить:');		
		//Button
		
			define('IMG_BTN_ROTATE_LEFT', '90&deg;CCW');
			define('IMG_BTN_ROTATE_RIGHT', '90&deg;CW');
			define('IMG_BTN_FLIP_H', 'Вертик.');
			define('IMG_BTN_FLIP_V', 'Гориз.');
			define('IMG_BTN_RESET', 'Сброс');
			define('IMG_BTN_UNDO', 'Откатить');
			define('IMG_BTN_SAVE', 'Сохранить');
			define('IMG_BTN_CLOSE', 'Закрыть');
			define('IMG_BTN_SAVE_AS', 'Сохранить как');
			define('IMG_BTN_CANCEL', 'Отменить');
		//Checkbox
			define('IMG_CHECKBOX_CONSTRAINT', 'Сохр.пропорции?');
		//Label
			define('IMG_LBL_WIDTH', 'Ширина:');
			define('IMG_LBL_HEIGHT', 'Высота:');
			define('IMG_LBL_X', 'X:');
			define('IMG_LBL_Y', 'Y:');
			define('IMG_LBL_RATIO', 'Соотношение:');
			define('IMG_LBL_ANGLE', 'Угол:');
			define('IMG_LBL_NEW_NAME', 'Имя:');
			define('IMG_LBL_SAVE_AS', 'Сохранить как');
			define('IMG_LBL_SAVE_TO', 'Папка:');
			define('IMG_LBL_ROOT_FOLDER', 'Корневая папка');
		//Editor
		//Save as 
		define('IMG_NEW_NAME_COMMENTS', 'Пожалуйста, не указывайте расширение в имени файла.');
		define('IMG_SAVE_AS_ERR_NAME_INVALID', 'Имя файла содержит недопустимые символы.');
		define('IMG_SAVE_AS_NOT_FOLDER_SELECTED', 'Не выбрана папка.');	
		define('IMG_SAVE_AS_FOLDER_NOT_FOUND', 'Такой папки не существует.');
		define('IMG_SAVE_AS_NEW_IMAGE_EXISTS', 'Файл с таким именем уже существует.');

		//Save
		define('IMG_SAVE_EMPTY_PATH', 'Не указан путь файла.');
		define('IMG_SAVE_NOT_EXISTS', 'Изображение не существует.');
		define('IMG_SAVE_PATH_DISALLOWED', 'У Вас отсутствует доступ к этому файлу.');
		define('IMG_SAVE_UNKNOWN_MODE', 'Unexpected Image Operation Mode');
		define('IMG_SAVE_RESIZE_FAILED', 'Не удалось изменить размер изображения.');
		define('IMG_SAVE_CROP_FAILED', 'Не удалось кадрировать изображение.');
		define('IMG_SAVE_FAILED', 'Не удалось сохранить изображение.');
		define('IMG_SAVE_BACKUP_FAILED', 'Не удалось восстановить изображение.');
		define('IMG_SAVE_ROTATE_FAILED', 'Не удалось повернуть изображение.');
		define('IMG_SAVE_FLIP_FAILED', 'Не удалось отазить изображение.');
		define('IMG_SAVE_SESSION_IMG_OPEN_FAILED', 'Не удалось открыть изображение из сессии.');
		define('IMG_SAVE_IMG_OPEN_FAILED', 'Не удалось открыть изображение.');
		
		
		//UNDO
		define('IMG_UNDO_NO_HISTORY_AVAIALBE', 'No history avaiable for undo.');
		define('IMG_UNDO_COPY_FAILED', 'Unable to restore the image.');
		define('IMG_UNDO_DEL_FAILED', 'Unable to delete the session image.');
	
	//88888888888   Above for Image Editor   888888888888888888888
	
	//88888888888   Session   888888888888888888888
		define('SESSION_PERSONAL_DIR_NOT_FOUND', 'Unable to find the dedicated folder which should have been created under session folder');
		define('SESSION_COUNTER_FILE_CREATE_FAILED', 'Unable to open the session counter file.');
		define('SESSION_COUNTER_FILE_WRITE_FAILED', 'Unable to write the session counter file.');
	//88888888888   Session   888888888888888888888
	
	//88888888888   Below for Text Editor   888888888888888888888
		define('TXT_FILE_NOT_FOUND', 'File is not found.');
		define('TXT_EXT_NOT_SELECTED', 'Please select file extension');
		define('TXT_DEST_FOLDER_NOT_SELECTED', 'Please select destination folder');
		define('TXT_UNKNOWN_REQUEST', 'Unknown Request.');
		define('TXT_DISALLOWED_EXT', 'You are allowed to edit/add such file type.');
		define('TXT_FILE_EXIST', 'Such file already exits.');
		define('TXT_FILE_NOT_EXIST', 'No such found.');
		define('TXT_CREATE_FAILED', 'Failed to create a new file.');
		define('TXT_CONTENT_WRITE_FAILED', 'Failed to write content to the file.');
		define('TXT_FILE_OPEN_FAILED', 'Failed to open the file.');
		define('TXT_CONTENT_UPDATE_FAILED', 'Failed to update content to the file.');
		define('TXT_SAVE_AS_ERR_NAME_INVALID', 'Please give it a name which only contain letters, digits, space, hyphen and underscore.');
	//88888888888   Above for Text Editor   888888888888888888888
	
	
?>