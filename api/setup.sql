CREATE DATABASE IF NOT EXISTS support
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

USE support;

CREATE TABLE IF NOT EXISTS tickets (
  id           VARCHAR(20)   NOT NULL PRIMARY KEY,
  date         DATE          NOT NULL,
  raised_by    VARCHAR(100)  NOT NULL,
  department   VARCHAR(100)  DEFAULT NULL,
  description  TEXT          NOT NULL,
  status       VARCHAR(50)   NOT NULL DEFAULT 'Open',
  solution     TEXT          DEFAULT NULL,
  resolver     VARCHAR(100)  DEFAULT NULL,
  resolve_date DATE          DEFAULT NULL,
  created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
