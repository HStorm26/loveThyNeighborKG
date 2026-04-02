-- Fix dbmessages id column missing AUTO_INCREMENT
ALTER TABLE dbmessages ADD PRIMARY KEY (id);
ALTER TABLE dbmessages MODIFY id INT NOT NULL AUTO_INCREMENT;

UPDATE dbevents SET location = REPLACE(location, '&#039;', "'");
UPDATE dbevents SET location = REPLACE(location, '&amp;', '&');
UPDATE dbevents SET location = REPLACE(location, '&quot;', '"');
UPDATE dbevents SET location = REPLACE(location, '&lt;', '<');
UPDATE dbevents SET location = REPLACE(location, '&gt;', '>');
-- run same for name and description if needed