-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sanfaani_schools
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_sessions`
--

DROP TABLE IF EXISTS `academic_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_sessions_school_id_name_unique` (`school_id`,`name`),
  CONSTRAINT `academic_sessions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_sessions`
--

LOCK TABLES `academic_sessions` WRITE;
/*!40000 ALTER TABLE `academic_sessions` DISABLE KEYS */;
INSERT INTO `academic_sessions` VALUES (1,1,'2025/2026','2025-01-01','2026-12-30',1,'active','2026-05-15 07:03:44','2026-05-15 07:03:44',NULL);
/*!40000 ALTER TABLE `academic_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_number_settings`
--

DROP TABLE IF EXISTS `admission_number_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_number_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `separator` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `year_format` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `next_number` int unsigned NOT NULL DEFAULT '1',
  `padding_length` tinyint unsigned NOT NULL DEFAULT '3',
  `suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_cycle` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'never',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admission_number_settings_school_id_unique` (`school_id`),
  KEY `admission_number_settings_school_id_status_index` (`school_id`,`status`),
  CONSTRAINT `admission_number_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_number_settings`
--

LOCK TABLES `admission_number_settings` WRITE;
/*!40000 ALTER TABLE `admission_number_settings` DISABLE KEYS */;
INSERT INTO `admission_number_settings` VALUES (1,1,'S','/','Y',2,3,NULL,'never','active',NULL,'2026-05-15 07:15:47','2026-05-15 07:16:49');
/*!40000 ALTER TABLE `admission_number_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `action` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_tag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auditable_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_school_id_action_index` (`school_id`,`action`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_tag_date_idx` (`action_tag`,`created_at`),
  KEY `audit_severity_date_idx` (`severity`,`created_at`),
  KEY `audit_logs_school_date_idx` (`school_id`,`created_at`),
  CONSTRAINT `audit_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,NULL,'mail_settings_updated','mail','info','App\\Models\\MailSetting',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"mailer\": \"smtp\", \"is_enabled\": true}','2026-05-15 06:49:01','2026-05-15 06:49:01'),(2,1,NULL,'mail_settings_test_sent','mail','info','App\\Models\\MailSetting',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"mailer\": \"smtp\"}','2026-05-15 06:49:22','2026-05-15 06:49:22'),(3,1,NULL,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',1,NULL,NULL,NULL,NULL,'{\"type\": \"school_onboarding\", \"recipient\": \"SALIHUTAOFEEKORIYOMI70@GMAIL.COM\", \"fallback_used\": false}','2026-05-15 06:50:49','2026-05-15 06:50:49'),(4,1,1,'school_admin_created','school','info','App\\Models\\User',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-15 06:54:32','2026-05-15 06:54:32'),(5,1,1,'support_access_started','support_access','notice','App\\Models\\School',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"role_context\": \"school_admin\", \"support_school_id\": 1}','2026-05-15 06:54:58','2026-05-15 06:54:58'),(6,1,1,'subject_assignment_created','subject','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"scope\": \"general\", \"created\": 1, \"subject_id\": \"1\"}','2026-05-15 06:57:05','2026-05-15 06:57:05'),(7,1,1,'support_access_continued','support_access','notice','App\\Models\\School',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"role_context\": \"school_admin\", \"support_school_id\": 1}','2026-05-15 06:57:16','2026-05-15 06:57:16'),(8,1,1,'support_access_started','support_access','notice','App\\Models\\School',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"role_context\": \"school_admin\", \"support_school_id\": 1}','2026-05-15 06:57:26','2026-05-15 06:57:26'),(9,1,1,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',2,NULL,NULL,NULL,NULL,'{\"type\": \"teacher_account_created\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"fallback_used\": false}','2026-05-15 06:58:58','2026-05-15 06:58:58'),(10,1,1,'staff_transactional_email_dispatched','staff','info','App\\Models\\User',2,NULL,NULL,NULL,NULL,'{\"event_key\": \"teacher_account_created\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"communication_log_id\": 2, \"communication_status\": \"sent\"}','2026-05-15 06:58:58','2026-05-15 06:58:58'),(11,1,1,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',3,NULL,NULL,NULL,NULL,'{\"type\": \"result_officer_account_created\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"fallback_used\": false}','2026-05-15 06:59:50','2026-05-15 06:59:50'),(12,1,1,'staff_transactional_email_dispatched','staff','info','App\\Models\\User',2,NULL,NULL,NULL,NULL,'{\"event_key\": \"result_officer_account_created\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"communication_log_id\": 3, \"communication_status\": \"sent\"}','2026-05-15 06:59:50','2026-05-15 06:59:50'),(13,1,1,'support_access_started','support_access','notice','App\\Models\\School',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"role_context\": \"school_admin\", \"support_school_id\": 1}','2026-05-15 07:04:48','2026-05-15 07:04:48'),(14,1,1,'school_public_page_updated','school','info','App\\Models\\SchoolPublicPage',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"slug\": \"sanfaani\"}','2026-05-15 07:06:16','2026-05-15 07:06:16'),(15,1,1,'support_access_stopped','support_access','warning','App\\Models\\School',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"started_at\": \"2026-05-15 08:04:48\", \"role_context\": \"school_admin\", \"last_confirmed_at\": \"2026-05-15 08:04:48\", \"support_school_id\": 1}','2026-05-15 07:15:07','2026-05-15 07:15:07'),(16,2,1,'teacher_class_assigned','teacher_assignment','info','App\\Models\\TeacherClassAssignment',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"ends_at\": null, \"role_type\": \"class_teacher\", \"starts_at\": null, \"subject_id\": null, \"school_class_id\": \"1\", \"teacher_user_id\": \"2\", \"assignment_scope\": \"class\"}','2026-05-15 07:20:05','2026-05-15 07:20:05'),(17,2,1,'teacher_subject_assigned','teacher_assignment','info','App\\Models\\TeacherSubjectAssignment',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"ends_at\": null, \"role_type\": \"subject_teacher\", \"starts_at\": null, \"subject_id\": \"1\", \"school_class_id\": \"1\", \"teacher_user_id\": \"2\", \"assignment_scope\": \"subject\"}','2026-05-15 07:20:33','2026-05-15 07:20:33'),(18,2,1,'scratch_card_request_created','scratch_card','info','App\\Models\\ScratchCardBatch',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"quantity\": \"50\", \"payment_method\": \"cash\"}','2026-05-15 07:21:40','2026-05-15 07:21:40'),(19,1,1,'communication_email_failed','communication','warning','App\\Models\\CommunicationLog',4,NULL,NULL,NULL,NULL,'{\"type\": \"subscription_activated\", \"error\": \"Connection could not be established with host \\\"smtp.gmail.com:587\\\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.gmail.com failed: No such host is known. \", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\"}','2026-05-15 07:27:22','2026-05-15 07:27:22'),(20,1,1,'school_notification_email_dispatched','school','info',NULL,NULL,NULL,NULL,NULL,NULL,'{\"event_key\": \"subscription_activated\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"target_role\": \"school_contact\", \"communication_log_id\": 4, \"communication_status\": \"failed\"}','2026-05-15 07:27:22','2026-05-15 07:27:22'),(21,2,1,'scratch_card_request_created','scratch_card','info','App\\Models\\ScratchCardBatch',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"quantity\": \"50\", \"payment_method\": \"cash\"}','2026-05-15 07:27:46','2026-05-15 07:27:46'),(22,1,1,'scratch_card_payment_confirmed','scratch_card','info','App\\Models\\ScratchCardBatch',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-15 07:36:17','2026-05-15 07:36:17'),(23,1,1,'scratch_card_payment_confirmed','scratch_card','info','App\\Models\\ScratchCardBatch',2,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-15 07:38:41','2026-05-15 07:38:41'),(24,1,NULL,'system_maintenance_clear_config_cache','system','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"output\": {\"config:clear\": \"INFO  Configuration cache cleared successfully.\"}, \"commands\": [\"config:clear\"]}','2026-05-15 07:41:46','2026-05-15 07:41:46'),(25,1,NULL,'system_maintenance_clear_route_cache','system','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"output\": {\"route:clear\": \"INFO  Route cache cleared successfully.\"}, \"commands\": [\"route:clear\"]}','2026-05-15 07:41:51','2026-05-15 07:41:51'),(26,1,NULL,'system_maintenance_storage_link','system','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"output\": {\"storage:link\": \"ERROR  The [C:\\\\laragon\\\\www\\\\sanfaani-schools\\\\public\\\\storage] link already exists.\"}, \"commands\": [\"storage:link\"]}','2026-05-15 07:41:56','2026-05-15 07:41:56'),(27,1,NULL,'system_maintenance_clear_view_cache','system','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"output\": {\"view:clear\": \"INFO  Compiled views cleared successfully.\"}, \"commands\": [\"view:clear\"]}','2026-05-15 07:42:02','2026-05-15 07:42:02'),(28,NULL,NULL,'system_maintenance_optimize_application','system','info',NULL,NULL,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"output\": {\"view:cache\": \"INFO  Blade templates cached successfully.\", \"route:cache\": \"\", \"config:cache\": \"\", \"optimize:clear\": \"INFO  Clearing cached bootstrap files.  \\n\\r\\n  config ............................................................................................... 149.87ms DONE\\r\\n  cache ................................................................................................ 222.90ms DONE\\r\\n  compiled ............................................................................................... 3.52ms DONE\\r\\n  events ................................................................................................. 1.40ms DONE\\r\\n  routes ................................................................................................. 1.31ms DONE\\r\\n  views ................................................................................................. 11.54ms DONE\"}, \"commands\": [\"optimize:clear\", \"config:cache\", \"route:cache\", \"view:cache\"]}','2026-05-15 07:42:26','2026-05-15 07:42:26'),(29,NULL,NULL,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',5,NULL,NULL,NULL,NULL,'{\"type\": \"lead_acknowledgment\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"fallback_used\": false}','2026-05-15 07:43:49','2026-05-15 07:43:49'),(30,NULL,NULL,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',6,NULL,NULL,NULL,NULL,'{\"type\": \"lead_admin_notification\", \"recipient\": \"admin@sanfaani.test\", \"fallback_used\": false}','2026-05-15 07:43:52','2026-05-15 07:43:52'),(31,NULL,NULL,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',7,NULL,NULL,NULL,NULL,'{\"type\": \"lead_acknowledgment\", \"recipient\": \"salihutaofeekoriyomi70@gmail.com\", \"fallback_used\": false}','2026-05-15 07:44:42','2026-05-15 07:44:42'),(32,NULL,NULL,'communication_email_sent','communication','info','App\\Models\\CommunicationLog',8,NULL,NULL,NULL,NULL,'{\"type\": \"lead_admin_notification\", \"recipient\": \"admin@sanfaani.test\", \"fallback_used\": false}','2026-05-15 07:44:45','2026-05-15 07:44:45'),(33,1,NULL,'lead_communication_recorded','lead','info','App\\Models\\LeadRequest',2,NULL,'{\"communication_log_id\": null, \"lead_communication_record_id\": 1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"actor_id\": 1, \"crm_workflow\": true}','2026-05-15 07:45:54','2026-05-15 07:45:54');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bulk_communication_batches`
--

DROP TABLE IF EXISTS `bulk_communication_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulk_communication_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned DEFAULT NULL,
  `audience` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channels` json NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_recipients` int unsigned NOT NULL DEFAULT '0',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `failed_count` int unsigned NOT NULL DEFAULT '0',
  `skipped_count` int unsigned NOT NULL DEFAULT '0',
  `duplicate_count` int unsigned NOT NULL DEFAULT '0',
  `chunk_size` smallint unsigned NOT NULL DEFAULT '25',
  `request_fingerprint` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bulk_communication_batches_batch_uuid_unique` (`batch_uuid`),
  KEY `bulk_communication_batches_sender_id_foreign` (`sender_id`),
  KEY `bulk_comm_batch_school_status_idx` (`school_id`,`status`,`created_at`),
  KEY `bulk_comm_batch_audience_idx` (`school_id`,`audience`,`created_at`),
  KEY `bulk_comm_batch_dedupe_idx` (`school_id`,`sender_id`,`request_fingerprint`),
  CONSTRAINT `bulk_communication_batches_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bulk_communication_batches_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bulk_communication_batches`
--

LOCK TABLES `bulk_communication_batches` WRITE;
/*!40000 ALTER TABLE `bulk_communication_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `bulk_communication_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bulk_communication_recipients`
--

DROP TABLE IF EXISTS `bulk_communication_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulk_communication_recipients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bulk_communication_batch_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `channel` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `recipient_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_id` bigint unsigned DEFAULT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `communication_log_id` bigint unsigned DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `fingerprint` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bulk_comm_recipient_unique` (`bulk_communication_batch_id`,`channel`,`fingerprint`),
  KEY `bulk_comm_recipient_status_idx` (`bulk_communication_batch_id`,`status`,`id`),
  KEY `bulk_comm_recipient_target_idx` (`school_id`,`recipient_type`,`recipient_id`),
  KEY `bulk_comm_recipient_log_idx` (`communication_log_id`),
  CONSTRAINT `bulk_comm_rec_batch_fk` FOREIGN KEY (`bulk_communication_batch_id`) REFERENCES `bulk_communication_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bulk_communication_recipients_communication_log_id_foreign` FOREIGN KEY (`communication_log_id`) REFERENCES `communication_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bulk_communication_recipients_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bulk_communication_recipients`
--

LOCK TABLES `bulk_communication_recipients` WRITE;
/*!40000 ALTER TABLE `bulk_communication_recipients` DISABLE KEYS */;
/*!40000 ALTER TABLE `bulk_communication_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_subject_assignments`
--

DROP TABLE IF EXISTS `class_subject_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_subject_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `assignment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `is_elective` tinyint(1) NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_subject_assignments_term_id_foreign` (`term_id`),
  KEY `csa_school_status_idx` (`school_id`,`status`),
  KEY `csa_class_subject_idx` (`school_class_id`,`subject_id`),
  KEY `csa_subject_status_idx` (`subject_id`,`status`),
  KEY `csa_session_term_idx` (`academic_session_id`,`term_id`),
  CONSTRAINT `class_subject_assignments_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `class_subject_assignments_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `class_subject_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subject_assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subject_assignments_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_subject_assignments`
--

LOCK TABLES `class_subject_assignments` WRITE;
/*!40000 ALTER TABLE `class_subject_assignments` DISABLE KEYS */;
INSERT INTO `class_subject_assignments` VALUES (1,1,NULL,1,NULL,NULL,'core',0,1,'active',NULL,'2026-05-15 06:57:04','2026-05-15 06:57:04',NULL);
/*!40000 ALTER TABLE `class_subject_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `communication_logs`
--

DROP TABLE IF EXISTS `communication_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communication_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `sender_id` bigint unsigned DEFAULT NULL,
  `sender_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `sender_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communication_logs_school_id_status_index` (`school_id`,`status`),
  KEY `comm_logs_school_type_created_idx` (`school_id`,`type`,`created_at`),
  KEY `comm_logs_sender_created_idx` (`sender_id`,`created_at`),
  KEY `comm_logs_status_created_idx` (`status`,`created_at`),
  KEY `comm_logs_sent_at_idx` (`sent_at`),
  KEY `comm_logs_recipient_idx` (`recipient`(191)),
  CONSTRAINT `communication_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `communication_logs_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `communication_logs`
--

LOCK TABLES `communication_logs` WRITE;
/*!40000 ALTER TABLE `communication_logs` DISABLE KEYS */;
INSERT INTO `communication_logs` VALUES (1,NULL,1,'user','super_admin','SALIHUTAOFEEKORIYOMI70@GMAIL.COM','Welcome to Sanfaani Schools','school_onboarding','sent',NULL,'2026-05-15 06:50:49','{\"category\": \"platform_transactional\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"school_id\": 1, \"queue_ready\": true, \"school_name\": \"sanfaani\", \"original_message\": \"Your school profile has been created successfully. You can now proceed with school admin onboarding and setup.\"}','2026-05-15 06:50:44','2026-05-15 06:50:49'),(2,1,1,'user','super_admin','salihutaofeekoriyomi70@gmail.com','Teacher account ready','teacher_account_created','sent',NULL,'2026-05-15 06:58:58','{\"role\": \"teacher\", \"category\": \"staff_lifecycle\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"login_id\": \"S/TCH/2026/001\", \"staff_id\": 2, \"event_key\": \"teacher_account_created\", \"action_url\": \"http://127.0.0.1:8000/login\", \"role_label\": \"Teacher\", \"queue_ready\": true, \"action_label\": \"Open Login\", \"original_message\": \"Your existing Sanfaani Schools account has been granted access to sanfaani.\\nSchool: sanfaani\\nRole: Teacher\\nLogin ID: S/TCH/2026/001\\nUse the password provided by your school admin, or request a password reset if you cannot sign in.\", \"was_existing_user\": true}','2026-05-15 06:58:54','2026-05-15 06:58:58'),(3,1,1,'user','super_admin','salihutaofeekoriyomi70@gmail.com','Result Officer account ready','result_officer_account_created','sent',NULL,'2026-05-15 06:59:50','{\"role\": \"result_officer\", \"category\": \"staff_lifecycle\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"login_id\": \"S/TCH/2026/001\", \"staff_id\": 2, \"event_key\": \"result_officer_account_created\", \"action_url\": \"http://127.0.0.1:8000/login\", \"role_label\": \"Result Officer\", \"queue_ready\": true, \"action_label\": \"Open Login\", \"original_message\": \"Your existing Sanfaani Schools account has been granted access to sanfaani.\\nSchool: sanfaani\\nRole: Result Officer\\nLogin ID: S/TCH/2026/001\\nUse the password provided by your school admin, or request a password reset if you cannot sign in.\", \"was_existing_user\": true}','2026-05-15 06:59:45','2026-05-15 06:59:50'),(4,1,1,'user','super_admin','salihutaofeekoriyomi70@gmail.com','Subscription activated','subscription_activated','failed','Connection could not be established with host \"smtp.gmail.com:587\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.gmail.com failed: No such host is known. ',NULL,'{\"status\": \"active\", \"ends_at\": null, \"category\": \"school_notification\", \"event_key\": \"subscription_activated\", \"plan_name\": \"tier\", \"starts_at\": \"2026-05-15\", \"action_url\": \"http://127.0.0.1:8000/school/subscription\", \"queue_ready\": true, \"target_role\": \"school_contact\", \"action_label\": \"View Subscription\", \"subscription_id\": 1, \"original_message\": \"Your school subscription has been activated.\\nPlan: tier\\nStatus: Active\\nBilling cycle: term\\nValid until: N/A\", \"recipient_source\": \"school_email\", \"recipient_user_id\": null, \"subscription_plan_id\": \"1\"}','2026-05-15 07:27:21','2026-05-15 07:27:22'),(5,NULL,NULL,'system',NULL,'salihutaofeekoriyomi70@gmail.com','We received your request','lead_acknowledgment','sent',NULL,'2026-05-15 07:43:49','{\"category\": \"platform_transactional\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"lead_type\": \"demo\", \"queue_ready\": true, \"original_message\": \"Thank you for your interest in Sanfaani Schools. Our team will contact you shortly.\"}','2026-05-15 07:43:45','2026-05-15 07:43:49'),(6,NULL,NULL,'system',NULL,'admin@sanfaani.test','New Demo request received','lead_admin_notification','sent',NULL,'2026-05-15 07:43:52','{\"category\": \"platform_transactional\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"lead_type\": \"demo\", \"queue_ready\": true, \"requester_email\": \"salihutaofeekoriyomi70@gmail.com\", \"original_message\": \"A new demo request has been submitted by HUSSEIN ALAMUTU.\"}','2026-05-15 07:43:49','2026-05-15 07:43:52'),(7,NULL,NULL,'system',NULL,'salihutaofeekoriyomi70@gmail.com','We received your request','lead_acknowledgment','sent',NULL,'2026-05-15 07:44:42','{\"category\": \"platform_transactional\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"lead_type\": \"contact\", \"queue_ready\": true, \"original_message\": \"Thank you for your interest in Sanfaani Schools. Our team will contact you shortly.\"}','2026-05-15 07:44:38','2026-05-15 07:44:42'),(8,NULL,NULL,'system',NULL,'admin@sanfaani.test','New Contact request received','lead_admin_notification','sent',NULL,'2026-05-15 07:44:45','{\"category\": \"platform_transactional\", \"delivery\": {\"fallback_used\": false, \"primary_error\": null}, \"lead_type\": \"contact\", \"queue_ready\": true, \"requester_email\": \"salihutaofeekoriyomi70@gmail.com\", \"original_message\": \"A new contact request has been submitted by monsurat saliu.\"}','2026-05-15 07:44:42','2026-05-15 07:44:45');
/*!40000 ALTER TABLE `communication_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grading_scales`
--

DROP TABLE IF EXISTS `grading_scales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grading_scales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default Grading Scale',
  `min_score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `grade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pass` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grading_scales_school_id_status_index` (`school_id`,`status`),
  CONSTRAINT `grading_scales_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grading_scales`
--

LOCK TABLES `grading_scales` WRITE;
/*!40000 ALTER TABLE `grading_scales` DISABLE KEYS */;
/*!40000 ALTER TABLE `grading_scales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language_preferences`
--

DROP TABLE IF EXISTS `language_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `language_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `scope_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope_id` bigint unsigned DEFAULT NULL,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_school_scope_idx` (`school_id`,`scope_type`),
  KEY `lang_scope_id_idx` (`scope_type`,`scope_id`),
  KEY `lang_code_status_idx` (`language_code`,`status`),
  CONSTRAINT `language_preferences_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_preferences`
--

LOCK TABLES `language_preferences` WRITE;
/*!40000 ALTER TABLE `language_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_communication_records`
--

DROP TABLE IF EXISTS `lead_communication_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_communication_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_request_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `communication_log_id` bigint unsigned DEFAULT NULL,
  `channel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `direction` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outbound',
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recorded',
  `communicated_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_communication_records_user_id_foreign` (`user_id`),
  KEY `lead_comm_lead_date_idx` (`lead_request_id`,`communicated_at`),
  KEY `lead_comm_log_idx` (`communication_log_id`),
  KEY `lead_comm_channel_status_idx` (`channel`,`status`),
  CONSTRAINT `lead_communication_records_communication_log_id_foreign` FOREIGN KEY (`communication_log_id`) REFERENCES `communication_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lead_communication_records_lead_request_id_foreign` FOREIGN KEY (`lead_request_id`) REFERENCES `lead_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_communication_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_communication_records`
--

LOCK TABLES `lead_communication_records` WRITE;
/*!40000 ALTER TABLE `lead_communication_records` DISABLE KEYS */;
INSERT INTO `lead_communication_records` VALUES (1,2,1,NULL,'email','outbound','salihutaofeekoriyomi70@gmail.com','hello',NULL,'recorded','2026-05-15 07:45:00','[]','2026-05-15 07:45:54','2026-05-15 07:45:54',NULL);
/*!40000 ALTER TABLE `lead_communication_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_notes`
--

DROP TABLE IF EXISTS `lead_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_request_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `note_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_notes_lead_date_idx` (`lead_request_id`,`created_at`),
  KEY `lead_notes_user_date_idx` (`user_id`,`created_at`),
  CONSTRAINT `lead_notes_lead_request_id_foreign` FOREIGN KEY (`lead_request_id`) REFERENCES `lead_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_notes`
--

LOCK TABLES `lead_notes` WRITE;
/*!40000 ALTER TABLE `lead_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_ownership_histories`
--

DROP TABLE IF EXISTS `lead_ownership_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_ownership_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_request_id` bigint unsigned NOT NULL,
  `old_assigned_to` bigint unsigned DEFAULT NULL,
  `new_assigned_to` bigint unsigned DEFAULT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_ownership_histories_old_assigned_to_foreign` (`old_assigned_to`),
  KEY `lead_ownership_histories_changed_by_foreign` (`changed_by`),
  KEY `lead_owner_history_lead_date_idx` (`lead_request_id`,`changed_at`),
  KEY `lead_owner_history_new_owner_idx` (`new_assigned_to`,`changed_at`),
  CONSTRAINT `lead_ownership_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lead_ownership_histories_lead_request_id_foreign` FOREIGN KEY (`lead_request_id`) REFERENCES `lead_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_ownership_histories_new_assigned_to_foreign` FOREIGN KEY (`new_assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lead_ownership_histories_old_assigned_to_foreign` FOREIGN KEY (`old_assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_ownership_histories`
--

LOCK TABLES `lead_ownership_histories` WRITE;
/*!40000 ALTER TABLE `lead_ownership_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_ownership_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_requests`
--

DROP TABLE IF EXISTS `lead_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_students` int unsigned DEFAULT NULL,
  `school_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_demo_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `assigned_to` bigint unsigned DEFAULT NULL,
  `contacted_at` timestamp NULL DEFAULT NULL,
  `next_follow_up_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `converted_by` bigint unsigned DEFAULT NULL,
  `converted_school_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `lost_reason` text COLLATE utf8mb4_unicode_ci,
  `archived_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_requests_type_status_index` (`type`,`status`),
  KEY `lead_requests_converted_by_foreign` (`converted_by`),
  KEY `lead_crm_status_followup_idx` (`status`,`next_follow_up_at`),
  KEY `lead_crm_assignee_followup_idx` (`assigned_to`,`next_follow_up_at`),
  KEY `lead_crm_conversion_idx` (`converted_school_id`,`converted_at`),
  KEY `lead_crm_created_status_idx` (`created_at`,`status`),
  CONSTRAINT `lead_requests_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lead_requests_converted_by_foreign` FOREIGN KEY (`converted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lead_requests_converted_school_id_foreign` FOREIGN KEY (`converted_school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_requests`
--

LOCK TABLES `lead_requests` WRITE;
/*!40000 ALTER TABLE `lead_requests` DISABLE KEYS */;
INSERT INTO `lead_requests` VALUES (1,'demo','HUSSEIN ALAMUTU','home','salihutaofeekoriyomi70@gmail.com','+2349161922695',NULL,78,'islamic','ggugu','fcgcg','landing_demo','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[]','2026-05-15 07:43:44','2026-05-15 07:43:44',NULL),(2,'contact','monsurat saliu','home','salihutaofeekoriyomi70@gmail.com','+2348146710120','n  j jkn',NULL,NULL,NULL,'h hkbjkb','landing_contact','new',NULL,'2026-05-15 07:45:54',NULL,'2026-05-15 07:45:54',NULL,NULL,NULL,NULL,NULL,NULL,'{\"page\": \"contact\"}','2026-05-15 07:44:38','2026-05-15 07:45:54',NULL);
/*!40000 ALTER TABLE `lead_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_timeline_events`
--

DROP TABLE IF EXISTS `lead_timeline_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_timeline_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_request_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `occurred_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_timeline_events_user_id_foreign` (`user_id`),
  KEY `lead_timeline_lead_date_idx` (`lead_request_id`,`occurred_at`),
  KEY `lead_timeline_type_date_idx` (`event_type`,`occurred_at`),
  CONSTRAINT `lead_timeline_events_lead_request_id_foreign` FOREIGN KEY (`lead_request_id`) REFERENCES `lead_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_timeline_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_timeline_events`
--

LOCK TABLES `lead_timeline_events` WRITE;
/*!40000 ALTER TABLE `lead_timeline_events` DISABLE KEYS */;
INSERT INTO `lead_timeline_events` VALUES (1,1,NULL,'created','Lead request submitted','Demo request received.','{\"source\": \"landing_demo\", \"lead_type\": \"demo\"}','2026-05-15 07:43:45','2026-05-15 07:43:45','2026-05-15 07:43:45'),(2,2,NULL,'created','Lead request submitted','Contact request received.','{\"source\": \"landing_contact\", \"lead_type\": \"contact\"}','2026-05-15 07:44:38','2026-05-15 07:44:38','2026-05-15 07:44:38'),(3,2,1,'communication_recorded','Communication recorded','hello','{\"status\": \"recorded\", \"channel\": \"email\", \"direction\": \"outbound\", \"communication_log_id\": null, \"lead_communication_record_id\": 1}','2026-05-15 07:45:54','2026-05-15 07:45:54','2026-05-15 07:45:54');
/*!40000 ALTER TABLE `lead_timeline_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_settings`
--

DROP TABLE IF EXISTS `mail_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `mailer` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'log',
  `host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` int unsigned DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `encryption` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_to_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_settings_school_id_is_enabled_index` (`school_id`,`is_enabled`),
  CONSTRAINT `mail_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_settings`
--

LOCK TABLES `mail_settings` WRITE;
/*!40000 ALTER TABLE `mail_settings` DISABLE KEYS */;
INSERT INTO `mail_settings` VALUES (1,NULL,'smtp','smtp.gmail.com',587,'sanfaanisaas@gmail.com','eyJpdiI6IlV6TWtsOTVweUlqcm1qUytNQUtwOEE9PSIsInZhbHVlIjoicFFrQXA5blJWOEh0WG1zZVVJVGlqd2djbmRRSnNZeXNzYkFGNExFRjFIMD0iLCJtYWMiOiI4YmNlNWJmNGZmOWI2YzY0OTEzNmQ5MTZjZWYwN2I0MzAxNDdjMTkxMzVkNjMzMDA4ZDAyODcyNTIyZjYxODc1IiwidGFnIjoiIn0=','tls','sanfaanisaas@gmail.com','Sanfaani Schools',NULL,1,NULL,'2026-05-15 06:43:31','2026-05-15 06:49:01'),(2,1,'smtp',NULL,NULL,NULL,NULL,NULL,'sanfaanisaas@gmail.com','Sanfaani Schools',NULL,0,NULL,'2026-05-15 06:58:54','2026-05-15 06:58:54');
/*!40000 ALTER TABLE `mail_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_30_125040_create_permission_tables',1),(5,'2026_04_30_141128_create_schools_table',1),(6,'2026_04_30_145058_add_school_id_to_users_table',1),(7,'2026_04_30_153808_create_school_classes_table',1),(8,'2026_04_30_162819_create_subjects_table',1),(9,'2026_04_30_170052_create_academic_sessions_table',1),(10,'2026_04_30_174909_create_terms_table',1),(11,'2026_04_30_185409_create_students_table',1),(12,'2026_04_30_201235_create_student_results_table',1),(13,'2026_05_01_004246_create_grading_scales_table',1),(14,'2026_05_01_004304_add_teacher_remark_to_student_results_table',1),(15,'2026_05_01_173852_add_publication_fields_to_student_results_table',1),(16,'2026_05_01_173857_create_result_publications_table',1),(17,'2026_05_01_204034_create_subscription_plans_table',1),(18,'2026_05_01_204053_create_plan_features_table',1),(19,'2026_05_01_204102_create_school_subscriptions_table',1),(20,'2026_05_01_204102_create_school_subscriptions_table',1),(21,'2026_05_01_204131_create_school_feature_overrides_table',2),(22,'2026_05_01_204143_create_school_result_access_policies_table',2),(23,'2026_05_01_204154_create_school_result_access_policy_rules_table',2),(24,'2026_05_01_204201_create_payment_transactions_table',2),(25,'2026_05_01_204208_create_scratch_card_batches_table',2),(26,'2026_05_01_204216_create_scratch_cards_table',2),(27,'2026_05_01_204229_create_scratch_card_usages_table',2),(28,'2026_05_02_000001_add_result_type_to_student_results_table',2),(29,'2026_05_02_000002_add_language_fields_to_schools_table',2),(30,'2026_05_02_000003_add_soft_deletes_to_pilot_data_tables',2),(31,'2026_05_02_000004_create_result_verifications_table',2),(32,'2026_05_02_000005_create_audit_logs_table',2),(33,'2026_05_02_000006_add_details_to_subscription_plans_table',2),(34,'2026_05_02_000007_update_student_result_unique_index_for_result_type',2),(35,'2026_05_03_000001_create_lead_requests_table',2),(36,'2026_05_03_000002_create_admission_number_settings_table',2),(37,'2026_05_03_000003_add_staff_code_to_users_table',2),(38,'2026_05_03_000004_add_school_code_to_schools_table',2),(39,'2026_05_03_000005_create_platform_settings_table',2),(40,'2026_05_03_000006_create_notification_preferences_table',2),(41,'2026_05_03_000007_create_student_class_enrollments_table',2),(42,'2026_05_03_000008_create_student_promotion_batches_table',2),(43,'2026_05_03_000009_create_student_promotion_items_table',2),(44,'2026_05_03_000010_create_report_card_templates_table',2),(45,'2026_05_03_000011_create_school_report_card_settings_table',2),(46,'2026_05_03_000012_create_report_card_comment_rules_table',2),(47,'2026_05_03_000013_add_pre_deployment_admin_fields',2),(48,'2026_05_05_000001_create_class_subject_assignments_table',2),(49,'2026_05_05_000002_create_student_elective_subjects_table',2),(50,'2026_05_05_000003_create_user_school_roles_table',2),(51,'2026_05_05_000004_add_v1_1_fields_to_classes_subjects_audit_logs',2),(52,'2026_05_05_000005_create_payment_gateway_settings_table',2),(53,'2026_05_05_000006_create_mail_settings_table',2),(54,'2026_05_05_000007_create_onboarding_progress_table',2),(55,'2026_05_05_000008_create_language_preferences_table',2),(56,'2026_05_05_000009_create_teacher_class_assignments_table',2),(57,'2026_05_05_000010_create_teacher_subject_assignments_table',2),(58,'2026_05_05_000011_create_teacher_result_submissions_table',2),(59,'2026_05_05_000012_create_school_public_pages_table',2),(60,'2026_05_05_000013_create_school_website_settings_table',2),(61,'2026_05_05_000014_create_support_threads_table',2),(62,'2026_05_05_000015_create_support_messages_table',2),(63,'2026_05_06_000001_add_must_change_password_to_users_table',2),(64,'2026_05_06_103647_create_school_role_feature_settings_table',2),(65,'2026_05_09_070000_add_school_scope_to_mail_settings_table',2),(66,'2026_05_09_070100_create_communication_logs_table',2),(67,'2026_05_12_000001_optimize_communication_logs_indexes',2),(68,'2026_05_13_000001_add_workflow_remarks_to_student_results_table',2),(69,'2026_05_13_000002_extend_student_class_enrollments_architecture',2),(70,'2026_05_13_000003_repair_auth_and_mail_schema_drift',2),(71,'2026_05_13_000004_repair_required_spatie_roles',2),(72,'2026_05_13_000005_stabilize_teacher_assignment_architecture',2),(73,'2026_05_13_000006_stabilize_student_academic_lifecycle',2),(74,'2026_05_13_000007_create_report_card_snapshots_table',2),(75,'2026_05_13_000008_create_bulk_communication_batches_table',2),(76,'2026_05_13_000009_upgrade_lead_requests_to_crm_workflow',2),(77,'2026_05_13_000010_add_hierarchical_support_routing',2),(78,'2026_05_14_000001_add_architecture_hardening_indexes',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',2),(4,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_preferences_school_id_channel_event_key_index` (`school_id`,`channel`,`event_key`),
  KEY `notification_preferences_user_id_channel_event_key_index` (`user_id`,`channel`,`event_key`),
  CONSTRAINT `notification_preferences_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_preferences`
--

LOCK TABLES `notification_preferences` WRITE;
/*!40000 ALTER TABLE `notification_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `onboarding_progress`
--

DROP TABLE IF EXISTS `onboarding_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `onboarding_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `context` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `step_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `onboard_step_unq` (`school_id`,`user_id`,`context`,`step_key`),
  KEY `onboard_school_context_idx` (`school_id`,`context`),
  KEY `onboard_user_context_idx` (`user_id`,`context`),
  CONSTRAINT `onboarding_progress_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `onboarding_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onboarding_progress`
--

LOCK TABLES `onboarding_progress` WRITE;
/*!40000 ALTER TABLE `onboarding_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `onboarding_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_gateway_settings`
--

DROP TABLE IF EXISTS `payment_gateway_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_gateway_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'test',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `public_key` text COLLATE utf8mb4_unicode_ci,
  `secret_key` text COLLATE utf8mb4_unicode_ci,
  `encryption_key` text COLLATE utf8mb4_unicode_ci,
  `webhook_secret` text COLLATE utf8mb4_unicode_ci,
  `callback_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pgs_gateway_mode_unq` (`gateway`,`mode`),
  KEY `pgs_enabled_mode_idx` (`is_enabled`,`mode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_gateway_settings`
--

LOCK TABLES `payment_gateway_settings` WRITE;
/*!40000 ALTER TABLE `payment_gateway_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_gateway_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `payable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payable_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `confirmed_by` bigint unsigned DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `payment_proof_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manual_payment_note` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_transactions_student_id_foreign` (`student_id`),
  KEY `payment_transactions_confirmed_by_foreign` (`confirmed_by`),
  KEY `payment_transactions_payable_index` (`payable_type`,`payable_id`),
  KEY `payment_transactions_school_status_index` (`school_id`,`status`),
  KEY `payment_transactions_gateway_reference_index` (`payment_gateway`,`gateway_reference`),
  KEY `payment_transactions_method_status_index` (`payment_method`,`status`),
  CONSTRAINT `payment_transactions_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (1,1,NULL,'App\\Models\\ScratchCardBatch',2,67.00,'NGN','cash',NULL,NULL,'CODEX-SMOKE-APPROVED','paid','2026-05-15 07:38:41',1,'2026-05-15 07:38:41',NULL,NULL,'{\"source\": \"scratch_card_batch_confirmation\"}','2026-05-15 07:36:17','2026-05-15 07:38:41');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plan_features`
--

DROP TABLE IF EXISTS `plan_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `feature_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `limit_value` int unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_features_plan_key_unique` (`subscription_plan_id`,`feature_key`),
  KEY `plan_features_key_enabled_index` (`feature_key`,`is_enabled`),
  CONSTRAINT `plan_features_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan_features`
--

LOCK TABLES `plan_features` WRITE;
/*!40000 ALTER TABLE `plan_features` DISABLE KEYS */;
INSERT INTO `plan_features` VALUES (1,1,'student_bulk_upload','Student Bulk Upload',1,NULL,NULL,'2026-05-15 07:25:57','2026-05-15 07:25:57'),(2,1,'manual_result_entry','Manual Result Entry',1,NULL,NULL,'2026-05-15 07:25:57','2026-05-15 07:25:57'),(3,1,'csv_result_upload','CSV Result Upload',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(4,1,'result_publishing','Result Publishing',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(5,1,'scratch_cards','Scratch Cards',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(6,1,'public_result_checker','Public Result Checker',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(7,1,'report_card_basic','Report Card Basic',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(8,1,'report_card_customization','Report Card Customization',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(9,1,'report_card_signature','Report Card Signatures',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(10,1,'report_card_auto_comments','Report Card Auto Comments',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(11,1,'report_card_pdf','Report Card PDF',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(12,1,'report_card_qr','Report Card QR',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(13,1,'report_card_templates','Report Card Templates',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(14,1,'result_access_policy','Result Access Policy',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(15,1,'payment_manual','Manual Payment',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(16,1,'payment_online','Online Payment',1,NULL,NULL,'2026-05-15 07:25:58','2026-05-15 07:25:58'),(17,1,'pdf_result','PDF Result',1,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(18,1,'qr_verification','QR Verification',1,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(19,1,'assessment_results','Assessment Results',1,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(20,1,'cbt_results','CBT Results',1,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(21,1,'sms_units','SMS Units',0,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(22,1,'mobile_app','Mobile App',0,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(23,1,'biometric_attendance','Biometric Attendance',0,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59'),(24,1,'website_customization','Website Customization',0,NULL,NULL,'2026-05-15 07:25:59','2026-05-15 07:25:59');
/*!40000 ALTER TABLE `plan_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platform_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sanfaani Schools',
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sanfaani Ltd',
  `product_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'https://schools.sanfaani.net',
  `main_company_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'https://sanfaani.net',
  `support_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sanfaanisaas@gmail.com',
  `sales_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sanfaanisaas@gmail.com',
  `support_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '09010172138',
  `whatsapp_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '+2349010172138',
  `default_country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nigeria',
  `default_currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `default_language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_background_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_settings`
--

LOCK TABLES `platform_settings` WRITE;
/*!40000 ALTER TABLE `platform_settings` DISABLE KEYS */;
INSERT INTO `platform_settings` VALUES (1,'Sanfaani Schools','Sanfaani Ltd','https://schools.sanfaani.net','https://sanfaani.net','sanfaanisaas@gmail.com','sanfaanisaas@gmail.com','+2349010172138','+2349010172138','Nigeria','NGN','en','platform/7iYq3PLyshzgEcuJ1YQXQMUX1CsAALNgEttfVTzd.png','platform/3dLuUvZ6wbpBcu45jODw0uycIOaJTi6BqVvvVHxV.png','platform/hOvr6iUrzPwojvSOakWvXPB24auzbuU5nFGrnYX9.png','{\"idle_timeout_minutes\": 30, \"public_page_template\": \"minimal\", \"public_pages_enabled\": true, \"public_result_checker_enabled\": true}','2026-05-14 18:28:53','2026-05-15 07:41:24');
/*!40000 ALTER TABLE `platform_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_card_comment_rules`
--

DROP TABLE IF EXISTS `report_card_comment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_card_comment_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `comment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_average` decimal(5,2) NOT NULL,
  `max_average` decimal(5,2) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_comment_school_type_idx` (`school_id`,`comment_type`),
  KEY `report_comment_school_status_idx` (`school_id`,`status`),
  KEY `report_comment_school_sort_idx` (`school_id`,`sort_order`),
  CONSTRAINT `report_card_comment_rules_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_card_comment_rules`
--

LOCK TABLES `report_card_comment_rules` WRITE;
/*!40000 ALTER TABLE `report_card_comment_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_card_comment_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_card_snapshots`
--

DROP TABLE IF EXISTS `report_card_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_card_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `term_id` bigint unsigned NOT NULL,
  `result_publication_id` bigint unsigned DEFAULT NULL,
  `result_verification_id` bigint unsigned DEFAULT NULL,
  `snapshot_version` int unsigned NOT NULL DEFAULT '1',
  `snapshot_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_report',
  `payload_schema_version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'report_card_snapshot_v1',
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `source_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `student_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admission_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_count` int unsigned NOT NULL DEFAULT '0',
  `total_score` decimal(8,2) NOT NULL DEFAULT '0.00',
  `average_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `student_snapshot` json NOT NULL,
  `school_snapshot` json NOT NULL,
  `academic_snapshot` json NOT NULL,
  `result_snapshot` json NOT NULL,
  `grading_snapshot` json DEFAULT NULL,
  `settings_snapshot` json DEFAULT NULL,
  `comments_snapshot` json DEFAULT NULL,
  `access_snapshot` json DEFAULT NULL,
  `snapshot_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_generated_at` timestamp NULL DEFAULT NULL,
  `generated_by` bigint unsigned DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_snapshot_context_version_unique` (`school_id`,`student_id`,`academic_session_id`,`term_id`,`result_type`,`snapshot_version`),
  UNIQUE KEY `report_card_snapshots_snapshot_uuid_unique` (`snapshot_uuid`),
  UNIQUE KEY `report_card_snapshots_snapshot_hash_unique` (`snapshot_hash`),
  KEY `report_card_snapshots_student_id_foreign` (`student_id`),
  KEY `report_card_snapshots_school_class_id_foreign` (`school_class_id`),
  KEY `report_card_snapshots_academic_session_id_foreign` (`academic_session_id`),
  KEY `report_card_snapshots_term_id_foreign` (`term_id`),
  KEY `report_card_snapshots_generated_by_foreign` (`generated_by`),
  KEY `report_snapshot_context_idx` (`school_id`,`school_class_id`,`academic_session_id`,`term_id`,`result_type`,`status`),
  KEY `report_snapshot_publication_student_idx` (`result_publication_id`,`student_id`),
  KEY `report_snapshot_verification_idx` (`result_verification_id`),
  KEY `report_snapshot_verification_code_idx` (`verification_code`),
  KEY `report_snapshot_admission_idx` (`school_id`,`admission_number`),
  CONSTRAINT `report_card_snapshots_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `report_card_snapshots_result_publication_id_foreign` FOREIGN KEY (`result_publication_id`) REFERENCES `result_publications` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_result_verification_id_foreign` FOREIGN KEY (`result_verification_id`) REFERENCES `result_verifications` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `report_card_snapshots_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_card_snapshots`
--

LOCK TABLES `report_card_snapshots` WRITE;
/*!40000 ALTER TABLE `report_card_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_card_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_card_templates`
--

DROP TABLE IF EXISTS `report_card_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_card_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `preview_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_card_templates_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_card_templates`
--

LOCK TABLES `report_card_templates` WRITE;
/*!40000 ALTER TABLE `report_card_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_card_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `result_publications`
--

DROP TABLE IF EXISTS `result_publications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_publications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `term_id` bigint unsigned NOT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `scope_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'class',
  `subject_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `scheduled_publish_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
  `unpublished_at` timestamp NULL DEFAULT NULL,
  `unpublished_by` bigint unsigned DEFAULT NULL,
  `unpublish_reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `result_publications_school_class_id_foreign` (`school_class_id`),
  KEY `result_publications_academic_session_id_foreign` (`academic_session_id`),
  KEY `result_publications_term_id_foreign` (`term_id`),
  KEY `result_publications_student_id_foreign` (`student_id`),
  KEY `result_publications_published_by_foreign` (`published_by`),
  KEY `result_publications_unpublished_by_foreign` (`unpublished_by`),
  KEY `result_publications_created_by_foreign` (`created_by`),
  KEY `result_publications_main_index` (`school_id`,`school_class_id`,`academic_session_id`,`term_id`,`result_type`,`scope_type`,`status`),
  KEY `result_publications_subject_student_index` (`subject_id`,`student_id`),
  CONSTRAINT `result_publications_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_publications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `result_publications_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `result_publications_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_publications_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_publications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `result_publications_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `result_publications_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_publications_unpublished_by_foreign` FOREIGN KEY (`unpublished_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `result_publications`
--

LOCK TABLES `result_publications` WRITE;
/*!40000 ALTER TABLE `result_publications` DISABLE KEYS */;
/*!40000 ALTER TABLE `result_publications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `result_verifications`
--

DROP TABLE IF EXISTS `result_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `term_id` bigint unsigned NOT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `verification_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `issued_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_verifications_context_unique` (`school_id`,`student_id`,`academic_session_id`,`term_id`,`result_type`),
  UNIQUE KEY `result_verifications_verification_code_unique` (`verification_code`),
  KEY `result_verifications_student_id_foreign` (`student_id`),
  KEY `result_verifications_academic_session_id_foreign` (`academic_session_id`),
  KEY `result_verifications_term_id_foreign` (`term_id`),
  KEY `result_verifications_revoked_by_foreign` (`revoked_by`),
  CONSTRAINT `result_verifications_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_verifications_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `result_verifications_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_verifications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_verifications_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `result_verifications`
--

LOCK TABLES `result_verifications` WRITE;
/*!40000 ALTER TABLE `result_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `result_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2026-05-14 18:16:34','2026-05-14 18:16:34'),(2,'school_admin','web','2026-05-14 18:16:34','2026-05-14 18:16:34'),(3,'result_officer','web','2026-05-14 18:16:34','2026-05-14 18:16:34'),(4,'teacher','web','2026-05-14 18:16:35','2026-05-14 18:16:35'),(5,'student','web','2026-05-14 18:16:35','2026-05-14 18:16:35');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_classes`
--

DROP TABLE IF EXISTS `school_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_classes_school_id_name_section_unique` (`school_id`,`name`,`section`),
  KEY `classes_school_code_idx` (`school_id`,`code`),
  CONSTRAINT `school_classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_classes`
--

LOCK TABLES `school_classes` WRITE;
/*!40000 ALTER TABLE `school_classes` DISABLE KEYS */;
INSERT INTO `school_classes` VALUES (1,1,'JSS 1',NULL,NULL,'active','2026-05-15 06:55:32','2026-05-15 06:55:32',NULL);
/*!40000 ALTER TABLE `school_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_feature_overrides`
--

DROP TABLE IF EXISTS `school_feature_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_feature_overrides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `feature_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `limit_value` int unsigned DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_feature_overrides_school_key_unique` (`school_id`,`feature_key`),
  KEY `school_feature_overrides_created_by_foreign` (`created_by`),
  KEY `school_feature_overrides_key_enabled_index` (`feature_key`,`is_enabled`),
  KEY `school_feature_overrides_period_index` (`starts_at`,`ends_at`),
  CONSTRAINT `school_feature_overrides_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_feature_overrides_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_feature_overrides`
--

LOCK TABLES `school_feature_overrides` WRITE;
/*!40000 ALTER TABLE `school_feature_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_feature_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_public_pages`
--

DROP TABLE IF EXISTS `school_public_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_public_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `result_checker_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `scratch_card_purchase_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `upcoming_events` json DEFAULT NULL,
  `extra_content` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_public_pages_slug_unique` (`slug`),
  KEY `spp_school_active_idx` (`school_id`,`is_active`),
  KEY `spp_slug_active_idx` (`slug`,`is_active`),
  CONSTRAINT `school_public_pages_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_public_pages`
--

LOCK TABLES `school_public_pages` WRITE;
/*!40000 ALTER TABLE `school_public_pages` DISABLE KEYS */;
INSERT INTO `school_public_pages` VALUES (1,1,'sanfaani',0,1,0,'sanfaani','sanfaani Result Checker','Use this page to access school result checking services.',NULL,NULL,'SALIHUTAOFEEKORIYOMI70@GMAIL.COM','+234-9121298324',NULL,'glorious command school',NULL,NULL,NULL,'2026-05-15 07:05:20','2026-05-15 07:05:20',NULL);
/*!40000 ALTER TABLE `school_public_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_report_card_settings`
--

DROP TABLE IF EXISTS `school_report_card_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_report_card_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `report_card_template_id` bigint unsigned DEFAULT NULL,
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accent_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_name_font` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'classic',
  `student_info_layout` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'two_column',
  `result_table_style` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `show_logo` tinyint(1) NOT NULL DEFAULT '1',
  `show_school_address` tinyint(1) NOT NULL DEFAULT '1',
  `show_school_phone` tinyint(1) NOT NULL DEFAULT '1',
  `show_school_email` tinyint(1) NOT NULL DEFAULT '1',
  `show_student_photo` tinyint(1) NOT NULL DEFAULT '0',
  `show_teacher_remark` tinyint(1) NOT NULL DEFAULT '1',
  `show_class_teacher` tinyint(1) NOT NULL DEFAULT '1',
  `show_head_teacher` tinyint(1) NOT NULL DEFAULT '1',
  `class_teacher_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_teacher_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_teacher_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_teacher_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_teacher_signature_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_teacher_signature_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_auto_class_teacher_comment` tinyint(1) NOT NULL DEFAULT '0',
  `enable_auto_head_teacher_comment` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_report_card_settings_school_id_unique` (`school_id`),
  KEY `school_report_card_settings_report_card_template_id_foreign` (`report_card_template_id`),
  CONSTRAINT `school_report_card_settings_report_card_template_id_foreign` FOREIGN KEY (`report_card_template_id`) REFERENCES `report_card_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_report_card_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_report_card_settings`
--

LOCK TABLES `school_report_card_settings` WRITE;
/*!40000 ALTER TABLE `school_report_card_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_report_card_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_result_access_policies`
--

DROP TABLE IF EXISTS `school_result_access_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_result_access_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `school_subscription_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_mode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scratch_card',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_result_access_policies_school_subscription_id_foreign` (`school_subscription_id`),
  KEY `school_result_access_policies_created_by_foreign` (`created_by`),
  KEY `result_access_policies_school_mode_status_index` (`school_id`,`access_mode`,`status`),
  KEY `result_access_policies_period_index` (`starts_at`,`ends_at`),
  CONSTRAINT `school_result_access_policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_result_access_policies_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_result_access_policies_school_subscription_id_foreign` FOREIGN KEY (`school_subscription_id`) REFERENCES `school_subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_result_access_policies`
--

LOCK TABLES `school_result_access_policies` WRITE;
/*!40000 ALTER TABLE `school_result_access_policies` DISABLE KEYS */;
INSERT INTO `school_result_access_policies` VALUES (1,1,NULL,'sanfaani','hybrid','active',NULL,NULL,NULL,1,NULL,'2026-05-15 07:26:33','2026-05-15 07:26:33');
/*!40000 ALTER TABLE `school_result_access_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_result_access_policy_rules`
--

DROP TABLE IF EXISTS `school_result_access_policy_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_result_access_policy_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_result_access_policy_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `access_scope` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term',
  `max_access_per_student` int unsigned DEFAULT NULL,
  `max_access_per_card` int unsigned DEFAULT NULL,
  `requires_scratch_card` tinyint(1) NOT NULL DEFAULT '1',
  `allows_parent_payment` tinyint(1) NOT NULL DEFAULT '0',
  `allows_school_paid_access` tinyint(1) NOT NULL DEFAULT '0',
  `allows_pdf_download` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_result_access_policy_rules_academic_session_id_foreign` (`academic_session_id`),
  KEY `school_result_access_policy_rules_term_id_foreign` (`term_id`),
  KEY `result_access_policy_rules_main_index` (`school_result_access_policy_id`,`academic_session_id`,`term_id`,`result_type`,`access_scope`,`status`),
  KEY `result_access_policy_rules_period_index` (`starts_at`,`ends_at`),
  CONSTRAINT `school_result_access_policy_rules_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_result_access_policy_rules_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sra_policy_rules_policy_fk` FOREIGN KEY (`school_result_access_policy_id`) REFERENCES `school_result_access_policies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_result_access_policy_rules`
--

LOCK TABLES `school_result_access_policy_rules` WRITE;
/*!40000 ALTER TABLE `school_result_access_policy_rules` DISABLE KEYS */;
INSERT INTO `school_result_access_policy_rules` VALUES (1,1,NULL,NULL,'term_result','term',NULL,NULL,1,1,1,0,'active',NULL,NULL,NULL,'2026-05-15 07:26:34','2026-05-15 07:26:34');
/*!40000 ALTER TABLE `school_result_access_policy_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_role_feature_settings`
--

DROP TABLE IF EXISTS `school_role_feature_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_role_feature_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `srfs_school_role_feature_unique` (`school_id`,`role_name`,`feature_key`),
  KEY `school_role_feature_settings_school_id_index` (`school_id`),
  KEY `school_role_feature_settings_role_name_index` (`role_name`),
  KEY `school_role_feature_settings_feature_key_index` (`feature_key`),
  CONSTRAINT `school_role_feature_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_role_feature_settings`
--

LOCK TABLES `school_role_feature_settings` WRITE;
/*!40000 ALTER TABLE `school_role_feature_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_role_feature_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_subscriptions`
--

DROP TABLE IF EXISTS `school_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `grace_ends_at` timestamp NULL DEFAULT NULL,
  `billing_cycle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term',
  `pricing_model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_student',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `student_count` int unsigned DEFAULT NULL,
  `amount_due` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_by` bigint unsigned DEFAULT NULL,
  `upgraded_from_subscription_id` bigint unsigned DEFAULT NULL,
  `downgraded_from_subscription_id` bigint unsigned DEFAULT NULL,
  `superseded_by_subscription_id` bigint unsigned DEFAULT NULL,
  `plan_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_snapshot` decimal(12,2) DEFAULT NULL,
  `billing_cycle_snapshot` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pricing_model_snapshot` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `features_snapshot` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_subscriptions_school_id_foreign` (`school_id`),
  KEY `school_subscriptions_subscription_plan_id_foreign` (`subscription_plan_id`),
  KEY `school_subscriptions_activated_by_foreign` (`activated_by`),
  CONSTRAINT `school_subscriptions_activated_by_foreign` FOREIGN KEY (`activated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_subscriptions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_subscriptions_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_subscriptions`
--

LOCK TABLES `school_subscriptions` WRITE;
/*!40000 ALTER TABLE `school_subscriptions` DISABLE KEYS */;
INSERT INTO `school_subscriptions` VALUES (1,1,1,'active','2026-05-14 23:00:00',NULL,NULL,NULL,'term','per_student',300.00,'NGN',NULL,0.00,0.00,'paid',NULL,1,NULL,NULL,NULL,'tier',300.00,'term','per_student','{\"sms_units\": {\"limit\": null, \"enabled\": false}, \"mobile_app\": {\"limit\": null, \"enabled\": false}, \"pdf_result\": {\"limit\": null, \"enabled\": true}, \"cbt_results\": {\"limit\": null, \"enabled\": true}, \"scratch_cards\": {\"limit\": null, \"enabled\": true}, \"payment_manual\": {\"limit\": null, \"enabled\": true}, \"payment_online\": {\"limit\": null, \"enabled\": true}, \"report_card_qr\": {\"limit\": null, \"enabled\": true}, \"qr_verification\": {\"limit\": null, \"enabled\": true}, \"report_card_pdf\": {\"limit\": null, \"enabled\": true}, \"csv_result_upload\": {\"limit\": null, \"enabled\": true}, \"report_card_basic\": {\"limit\": null, \"enabled\": true}, \"result_publishing\": {\"limit\": null, \"enabled\": true}, \"assessment_results\": {\"limit\": null, \"enabled\": true}, \"manual_result_entry\": {\"limit\": null, \"enabled\": true}, \"student_bulk_upload\": {\"limit\": null, \"enabled\": true}, \"biometric_attendance\": {\"limit\": null, \"enabled\": false}, \"result_access_policy\": {\"limit\": null, \"enabled\": true}, \"public_result_checker\": {\"limit\": null, \"enabled\": true}, \"report_card_signature\": {\"limit\": null, \"enabled\": true}, \"report_card_templates\": {\"limit\": null, \"enabled\": true}, \"website_customization\": {\"limit\": null, \"enabled\": false}, \"report_card_auto_comments\": {\"limit\": null, \"enabled\": true}, \"report_card_customization\": {\"limit\": null, \"enabled\": true}}',NULL,'2026-05-15 07:27:21','2026-05-15 07:27:21');
/*!40000 ALTER TABLE `school_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_website_settings`
--

DROP TABLE IF EXISTS `school_website_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_website_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `website_mode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'result_link_only',
  `website_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `result_checker_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `preferred_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subdomain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `homepage_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `events_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `announcements_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `admissions_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `contact_page_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sws_school_unq` (`school_id`),
  KEY `sws_mode_enabled_idx` (`website_mode`,`website_enabled`),
  CONSTRAINT `school_website_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_website_settings`
--

LOCK TABLES `school_website_settings` WRITE;
/*!40000 ALTER TABLE `school_website_settings` DISABLE KEYS */;
INSERT INTO `school_website_settings` VALUES (1,1,'result_link_only',0,1,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,'2026-05-15 07:05:20','2026-05-15 07:05:20');
/*!40000 ALTER TABLE `school_website_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `subscription_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `default_language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `supports_rtl` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_slug_unique` (`slug`),
  UNIQUE KEY `schools_school_code_unique` (`school_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'sanfaani','sanfaani','S-SCH-0001','SALIHUTAOFEEKORIYOMI70@GMAIL.COM','+234-9121298324','glorious command school','schools/logos/wOPNN8LZkrvnrDFbJfl1JR0eVcIte0JAi9f929Hn.png','active','active','en',1,'2026-05-15 06:50:44','2026-05-15 06:50:44',NULL);
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scratch_card_batches`
--

DROP TABLE IF EXISTS `scratch_card_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scratch_card_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_result_access_policy_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_confirmed_at` timestamp NULL DEFAULT NULL,
  `payment_confirmed_by` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `expires_at` timestamp NULL DEFAULT NULL,
  `generated_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scratch_card_batches_school_class_id_foreign` (`school_class_id`),
  KEY `scratch_card_batches_academic_session_id_foreign` (`academic_session_id`),
  KEY `scratch_card_batches_term_id_foreign` (`term_id`),
  KEY `scratch_card_batches_school_result_access_policy_id_foreign` (`school_result_access_policy_id`),
  KEY `scratch_card_batches_payment_confirmed_by_foreign` (`payment_confirmed_by`),
  KEY `scratch_card_batches_generated_by_foreign` (`generated_by`),
  KEY `scratch_card_batches_main_index` (`school_id`,`academic_session_id`,`term_id`,`result_type`,`payment_status`,`status`),
  KEY `scratch_card_batches_school_class_index` (`school_id`,`school_class_id`),
  CONSTRAINT `scratch_card_batches_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_batches_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_batches_payment_confirmed_by_foreign` FOREIGN KEY (`payment_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_batches_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_batches_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scratch_card_batches_school_result_access_policy_id_foreign` FOREIGN KEY (`school_result_access_policy_id`) REFERENCES `school_result_access_policies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_batches_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scratch_card_batches`
--

LOCK TABLES `scratch_card_batches` WRITE;
/*!40000 ALTER TABLE `scratch_card_batches` DISABLE KEYS */;
INSERT INTO `scratch_card_batches` VALUES (1,1,1,1,1,'term_result',NULL,NULL,50,0.00,'NGN','manual_pending','cash','CODEX-SMOKE-APPROVED',NULL,NULL,'pending_payment',NULL,NULL,'{\"request_note\": null, \"requested_at\": \"2026-05-15 08:21:40\", \"requested_by\": 2}','2026-05-15 07:21:40','2026-05-15 07:21:40',NULL),(2,1,1,1,1,'term_result',NULL,'fistrt',50,67.00,'NGN','paid','cash','CODEX-SMOKE-APPROVED','2026-05-15 07:38:41',1,'pending_payment',NULL,NULL,'{\"request_note\": null, \"requested_at\": \"2026-05-15 08:27:45\", \"requested_by\": 2}','2026-05-15 07:27:45','2026-05-15 07:38:41',NULL);
/*!40000 ALTER TABLE `scratch_card_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scratch_card_usages`
--

DROP TABLE IF EXISTS `scratch_card_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scratch_card_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scratch_card_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scratch_card_usages_student_id_foreign` (`student_id`),
  KEY `scratch_card_usages_academic_session_id_foreign` (`academic_session_id`),
  KEY `scratch_card_usages_term_id_foreign` (`term_id`),
  KEY `scratch_card_usages_card_student_index` (`scratch_card_id`,`student_id`),
  KEY `scratch_card_usages_result_context_index` (`school_id`,`academic_session_id`,`term_id`,`result_type`),
  KEY `scratch_usage_card_context_idx` (`scratch_card_id`,`academic_session_id`,`term_id`,`result_type`),
  KEY `scratch_usage_student_context_idx` (`school_id`,`student_id`,`academic_session_id`,`term_id`,`result_type`),
  CONSTRAINT `scratch_card_usages_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_usages_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scratch_card_usages_scratch_card_id_foreign` FOREIGN KEY (`scratch_card_id`) REFERENCES `scratch_cards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scratch_card_usages_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_card_usages_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scratch_card_usages`
--

LOCK TABLES `scratch_card_usages` WRITE;
/*!40000 ALTER TABLE `scratch_card_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `scratch_card_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scratch_cards`
--

DROP TABLE IF EXISTS `scratch_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scratch_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scratch_card_batch_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin_code` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_uses` int unsigned NOT NULL DEFAULT '1',
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unused',
  `used_by_student_id` bigint unsigned DEFAULT NULL,
  `first_used_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `revoke_reason` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `generated_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scratch_cards_serial_number_unique` (`serial_number`),
  KEY `scratch_cards_school_class_id_foreign` (`school_class_id`),
  KEY `scratch_cards_academic_session_id_foreign` (`academic_session_id`),
  KEY `scratch_cards_term_id_foreign` (`term_id`),
  KEY `scratch_cards_used_by_student_id_foreign` (`used_by_student_id`),
  KEY `scratch_cards_revoked_by_foreign` (`revoked_by`),
  KEY `scratch_cards_generated_by_foreign` (`generated_by`),
  KEY `scratch_cards_main_index` (`school_id`,`academic_session_id`,`term_id`,`result_type`,`status`),
  KEY `scratch_cards_serial_status_index` (`serial_number`,`status`),
  KEY `scratch_cards_school_student_index` (`school_id`,`used_by_student_id`),
  KEY `scratch_cards_pin_hash_index` (`pin_hash`),
  KEY `scratch_cards_batch_status_idx` (`scratch_card_batch_id`,`status`),
  CONSTRAINT `scratch_cards_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_cards_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_cards_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_cards_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_cards_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scratch_cards_scratch_card_batch_id_foreign` FOREIGN KEY (`scratch_card_batch_id`) REFERENCES `scratch_card_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scratch_cards_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scratch_cards_used_by_student_id_foreign` FOREIGN KEY (`used_by_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scratch_cards`
--

LOCK TABLES `scratch_cards` WRITE;
/*!40000 ALTER TABLE `scratch_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `scratch_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_class_enrollments`
--

DROP TABLE IF EXISTS `student_class_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_class_enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `start_term_id` bigint unsigned DEFAULT NULL,
  `end_term_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `promoted_from_enrollment_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_class_enrollments_school_class_id_foreign` (`school_class_id`),
  KEY `student_class_enrollments_academic_session_id_foreign` (`academic_session_id`),
  KEY `stu_enroll_student_session_idx` (`school_id`,`student_id`,`academic_session_id`),
  KEY `stu_enroll_class_status_idx` (`school_id`,`school_class_id`,`academic_session_id`,`status`),
  KEY `stu_enroll_student_fk_idx` (`student_id`),
  KEY `student_class_enrollments_start_term_id_foreign` (`start_term_id`),
  KEY `student_class_enrollments_end_term_id_foreign` (`end_term_id`),
  KEY `student_class_enrollments_created_by_foreign` (`created_by`),
  KEY `stu_enroll_student_status_session_idx` (`school_id`,`student_id`,`status`,`academic_session_id`),
  KEY `stu_enroll_history_lookup_idx` (`school_id`,`student_id`,`school_class_id`,`academic_session_id`,`start_term_id`),
  KEY `stu_enroll_current_lookup_idx` (`school_id`,`student_id`,`status`,`end_term_id`),
  KEY `stu_enroll_lineage_idx` (`promoted_from_enrollment_id`),
  CONSTRAINT `stu_enroll_from_fk` FOREIGN KEY (`promoted_from_enrollment_id`) REFERENCES `student_class_enrollments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_class_enrollments_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_class_enrollments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_class_enrollments_end_term_id_foreign` FOREIGN KEY (`end_term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_class_enrollments_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_class_enrollments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_class_enrollments_start_term_id_foreign` FOREIGN KEY (`start_term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_class_enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_class_enrollments`
--

LOCK TABLES `student_class_enrollments` WRITE;
/*!40000 ALTER TABLE `student_class_enrollments` DISABLE KEYS */;
INSERT INTO `student_class_enrollments` VALUES (1,1,1,1,1,1,NULL,'active',2,'2026-05-15 07:16:49',NULL,'{\"source\": \"student_created\"}','2026-05-15 07:16:49','2026-05-15 07:16:49');
/*!40000 ALTER TABLE `student_class_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_elective_subjects`
--

DROP TABLE IF EXISTS `student_elective_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_elective_subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_elective_subjects_subject_id_foreign` (`subject_id`),
  KEY `student_elective_subjects_academic_session_id_foreign` (`academic_session_id`),
  KEY `student_elective_subjects_term_id_foreign` (`term_id`),
  KEY `stu_elec_school_status_idx` (`school_id`,`status`),
  KEY `stu_elec_student_subject_idx` (`student_id`,`subject_id`),
  KEY `stu_elec_class_status_idx` (`school_class_id`,`status`),
  CONSTRAINT `student_elective_subjects_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_elective_subjects_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_elective_subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_elective_subjects_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_elective_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_elective_subjects_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_elective_subjects`
--

LOCK TABLES `student_elective_subjects` WRITE;
/*!40000 ALTER TABLE `student_elective_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_elective_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_promotion_batches`
--

DROP TABLE IF EXISTS `student_promotion_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_promotion_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `from_academic_session_id` bigint unsigned NOT NULL,
  `to_academic_session_id` bigint unsigned NOT NULL,
  `from_school_class_id` bigint unsigned NOT NULL,
  `to_school_class_id` bigint unsigned DEFAULT NULL,
  `promotion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `created_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_promotion_batches_from_academic_session_id_foreign` (`from_academic_session_id`),
  KEY `student_promotion_batches_to_academic_session_id_foreign` (`to_academic_session_id`),
  KEY `student_promotion_batches_from_school_class_id_foreign` (`from_school_class_id`),
  KEY `student_promotion_batches_to_school_class_id_foreign` (`to_school_class_id`),
  KEY `student_promotion_batches_created_by_foreign` (`created_by`),
  KEY `promo_batch_from_idx` (`school_id`,`from_academic_session_id`,`from_school_class_id`),
  KEY `promo_batch_to_idx` (`school_id`,`to_academic_session_id`,`to_school_class_id`),
  CONSTRAINT `student_promotion_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_promotion_batches_from_academic_session_id_foreign` FOREIGN KEY (`from_academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_batches_from_school_class_id_foreign` FOREIGN KEY (`from_school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_batches_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_batches_to_academic_session_id_foreign` FOREIGN KEY (`to_academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_batches_to_school_class_id_foreign` FOREIGN KEY (`to_school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_promotion_batches`
--

LOCK TABLES `student_promotion_batches` WRITE;
/*!40000 ALTER TABLE `student_promotion_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_promotion_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_promotion_items`
--

DROP TABLE IF EXISTS `student_promotion_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_promotion_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_promotion_batch_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `from_school_class_id` bigint unsigned NOT NULL,
  `to_school_class_id` bigint unsigned DEFAULT NULL,
  `from_academic_session_id` bigint unsigned NOT NULL,
  `to_academic_session_id` bigint unsigned NOT NULL,
  `from_student_class_enrollment_id` bigint unsigned DEFAULT NULL,
  `to_student_class_enrollment_id` bigint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_promotion_items_student_promotion_batch_id_foreign` (`student_promotion_batch_id`),
  KEY `student_promotion_items_student_id_foreign` (`student_id`),
  KEY `student_promotion_items_from_school_class_id_foreign` (`from_school_class_id`),
  KEY `student_promotion_items_to_school_class_id_foreign` (`to_school_class_id`),
  KEY `student_promotion_items_from_academic_session_id_foreign` (`from_academic_session_id`),
  KEY `student_promotion_items_to_academic_session_id_foreign` (`to_academic_session_id`),
  KEY `promo_item_school_student_idx` (`school_id`,`student_id`),
  KEY `promo_item_school_action_idx` (`school_id`,`action`),
  KEY `promo_item_school_status_idx` (`school_id`,`status`),
  KEY `student_promotion_items_to_student_class_enrollment_id_foreign` (`to_student_class_enrollment_id`),
  KEY `promo_item_lifecycle_idx` (`school_id`,`student_id`,`action`,`status`),
  KEY `promo_item_enrollment_lineage_idx` (`from_student_class_enrollment_id`,`to_student_class_enrollment_id`),
  CONSTRAINT `student_promotion_items_from_academic_session_id_foreign` FOREIGN KEY (`from_academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_from_school_class_id_foreign` FOREIGN KEY (`from_school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_from_student_class_enrollment_id_foreign` FOREIGN KEY (`from_student_class_enrollment_id`) REFERENCES `student_class_enrollments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_promotion_items_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_student_promotion_batch_id_foreign` FOREIGN KEY (`student_promotion_batch_id`) REFERENCES `student_promotion_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_to_academic_session_id_foreign` FOREIGN KEY (`to_academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_promotion_items_to_school_class_id_foreign` FOREIGN KEY (`to_school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_promotion_items_to_student_class_enrollment_id_foreign` FOREIGN KEY (`to_student_class_enrollment_id`) REFERENCES `student_class_enrollments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_promotion_items`
--

LOCK TABLES `student_promotion_items` WRITE;
/*!40000 ALTER TABLE `student_promotion_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_promotion_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_results`
--

DROP TABLE IF EXISTS `student_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `term_id` bigint unsigned NOT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `ca_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `exam_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_remark` text COLLATE utf8mb4_unicode_ci,
  `officer_remark` text COLLATE utf8mb4_unicode_ci,
  `admin_remark` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
  `unpublished_at` timestamp NULL DEFAULT NULL,
  `unpublished_by` bigint unsigned DEFAULT NULL,
  `unpublish_reason` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `teacher_result_submission_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_subject_term_type_result` (`school_id`,`student_id`,`subject_id`,`academic_session_id`,`term_id`,`result_type`),
  KEY `student_results_student_id_foreign` (`student_id`),
  KEY `student_results_school_class_id_foreign` (`school_class_id`),
  KEY `student_results_subject_id_foreign` (`subject_id`),
  KEY `student_results_academic_session_id_foreign` (`academic_session_id`),
  KEY `student_results_term_id_foreign` (`term_id`),
  KEY `student_results_recorded_by_foreign` (`recorded_by`),
  KEY `student_results_published_by_foreign` (`published_by`),
  KEY `student_results_unpublished_by_foreign` (`unpublished_by`),
  KEY `student_results_publish_index` (`school_id`,`school_class_id`,`academic_session_id`,`term_id`,`status`),
  KEY `sr_teacher_submission_idx` (`teacher_result_submission_id`),
  KEY `student_results_public_lookup_idx` (`school_id`,`student_id`,`academic_session_id`,`term_id`,`result_type`,`status`,`published_at`,`unpublished_at`),
  KEY `student_results_publish_scope_idx` (`school_id`,`school_class_id`,`academic_session_id`,`term_id`,`result_type`,`status`),
  CONSTRAINT `student_results_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_results_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_results_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_results_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_results_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_results_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_results_teacher_result_submission_id_foreign` FOREIGN KEY (`teacher_result_submission_id`) REFERENCES `teacher_result_submissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_results_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_results_unpublished_by_foreign` FOREIGN KEY (`unpublished_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_results`
--

LOCK TABLES `student_results` WRITE;
/*!40000 ALTER TABLE `student_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `admission_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `guardian_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_school_id_admission_number_unique` (`school_id`,`admission_number`),
  KEY `students_school_class_id_foreign` (`school_class_id`),
  KEY `students_school_status_archive_idx` (`school_id`,`status`,`deleted_at`),
  CONSTRAINT `students_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,1,1,'S/2026/001','HUSSEIN','Olawale','ALAMUTU','male','2014-06-13','Hussein','09161922695','alamutuhussein@gmail.com','PLOT 10B ISHOLA LUQMAN GEREWU ISLAMIC VIAGE AFTEE IQRA JUNCTION\r\nPLOT 10B ISHOLA LUQMAN GEREWU ISLAMIC VIAGE AFTEE IQRA JUNCTION','active','2026-05-15 07:16:49','2026-05-15 07:16:49',NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `is_elective` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_school_id_name_unique` (`school_id`,`name`),
  UNIQUE KEY `subjects_school_id_code_unique` (`school_id`,`code`),
  KEY `subjects_school_type_idx` (`school_id`,`assignment_type`),
  CONSTRAINT `subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,'English Language',NULL,'core',0,'active','2026-05-15 06:56:47','2026-05-15 06:56:47',NULL);
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `pricing_model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_student',
  `billing_cycle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term',
  `duration_days` int unsigned DEFAULT NULL,
  `is_trial` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'tier','tier','yyy',300.00,'NGN','per_student','term',360,0,'active',0,NULL,'2026-05-15 07:25:57','2026-05-15 07:25:57');
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_escalation_histories`
--

DROP TABLE IF EXISTS `support_escalation_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_escalation_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_thread_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `escalated_by` bigint unsigned DEFAULT NULL,
  `from_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'super_admin',
  `from_level` tinyint unsigned NOT NULL DEFAULT '0',
  `to_level` tinyint unsigned NOT NULL DEFAULT '1',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_escalation_histories_escalated_by_foreign` (`escalated_by`),
  KEY `seh_thread_date_idx` (`support_thread_id`,`escalated_at`),
  KEY `seh_school_date_idx` (`school_id`,`escalated_at`),
  KEY `seh_role_date_idx` (`to_role`,`escalated_at`),
  CONSTRAINT `support_escalation_histories_escalated_by_foreign` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_escalation_histories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_escalation_histories_support_thread_id_foreign` FOREIGN KEY (`support_thread_id`) REFERENCES `support_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_escalation_histories`
--

LOCK TABLES `support_escalation_histories` WRITE;
/*!40000 ALTER TABLE `support_escalation_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_escalation_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_messages`
--

DROP TABLE IF EXISTS `support_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_thread_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `sender_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_internal_note` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smsg_thread_date_idx` (`support_thread_id`,`created_at`),
  KEY `smsg_school_date_idx` (`school_id`,`created_at`),
  KEY `smsg_sender_date_idx` (`sender_id`,`created_at`),
  CONSTRAINT `support_messages_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_support_thread_id_foreign` FOREIGN KEY (`support_thread_id`) REFERENCES `support_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_messages`
--

LOCK TABLES `support_messages` WRITE;
/*!40000 ALTER TABLE `support_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_thread_events`
--

DROP TABLE IF EXISTS `support_thread_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_thread_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_thread_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `actor_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `occurred_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_thread_events_actor_id_foreign` (`actor_id`),
  KEY `ste_thread_date_idx` (`support_thread_id`,`occurred_at`),
  KEY `ste_school_date_idx` (`school_id`,`occurred_at`),
  KEY `ste_type_date_idx` (`event_type`,`occurred_at`),
  CONSTRAINT `support_thread_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_thread_events_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_thread_events_support_thread_id_foreign` FOREIGN KEY (`support_thread_id`) REFERENCES `support_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_thread_events`
--

LOCK TABLES `support_thread_events` WRITE;
/*!40000 ALTER TABLE `support_thread_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_thread_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_threads`
--

DROP TABLE IF EXISTS `support_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_threads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `creator_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `routed_to_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `visibility` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `escalation_level` tinyint unsigned NOT NULL DEFAULT '0',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sth_school_status_idx` (`school_id`,`status`),
  KEY `sth_status_priority_idx` (`status`,`priority`),
  KEY `sth_last_msg_idx` (`last_message_at`),
  KEY `support_threads_escalated_by_foreign` (`escalated_by`),
  KEY `sth_school_route_status_idx` (`school_id`,`routed_to_role`,`status`),
  KEY `sth_creator_status_idx` (`created_by`,`status`),
  KEY `sth_escalation_idx` (`escalation_level`,`escalated_at`),
  KEY `sth_assignee_status_idx` (`assigned_to`,`status`),
  CONSTRAINT `support_threads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_threads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_threads_escalated_by_foreign` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_threads_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_threads`
--

LOCK TABLES `support_threads` WRITE;
/*!40000 ALTER TABLE `support_threads` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_threads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_update_logs`
--

DROP TABLE IF EXISTS `system_update_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_update_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded',
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `package_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_update_logs_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `system_update_logs_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_update_logs`
--

LOCK TABLES `system_update_logs` WRITE;
/*!40000 ALTER TABLE `system_update_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_update_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_class_assignments`
--

DROP TABLE IF EXISTS `teacher_class_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_class_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `teacher_user_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `role_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'class_teacher',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_class_assignments_term_id_foreign` (`term_id`),
  KEY `teacher_class_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `tca_school_status_idx` (`school_id`,`status`),
  KEY `tca_teacher_status_idx` (`teacher_user_id`,`status`),
  KEY `tca_class_status_idx` (`school_class_id`,`status`),
  KEY `tca_school_teacher_class_idx` (`school_id`,`teacher_user_id`,`status`,`school_class_id`),
  KEY `tca_school_class_role_idx` (`school_id`,`school_class_id`,`role_type`,`status`),
  KEY `tca_context_status_idx` (`academic_session_id`,`term_id`,`status`),
  CONSTRAINT `teacher_class_assignments_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_class_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_class_assignments_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_teacher_user_id_foreign` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_assignments_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_class_assignments`
--

LOCK TABLES `teacher_class_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_class_assignments` DISABLE KEYS */;
INSERT INTO `teacher_class_assignments` VALUES (1,1,2,1,1,1,NULL,NULL,'class_teacher','active',2,NULL,'2026-05-15 07:20:05','2026-05-15 07:20:05',NULL);
/*!40000 ALTER TABLE `teacher_class_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_result_submissions`
--

DROP TABLE IF EXISTS `teacher_result_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_result_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `teacher_user_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `term_id` bigint unsigned NOT NULL,
  `result_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'term_result',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `returned_by` bigint unsigned DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `return_reason` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_result_submissions_subject_id_foreign` (`subject_id`),
  KEY `teacher_result_submissions_term_id_foreign` (`term_id`),
  KEY `teacher_result_submissions_reviewed_by_foreign` (`reviewed_by`),
  KEY `teacher_result_submissions_approved_by_foreign` (`approved_by`),
  KEY `teacher_result_submissions_published_by_foreign` (`published_by`),
  KEY `teacher_result_submissions_returned_by_foreign` (`returned_by`),
  KEY `trs_school_status_idx` (`school_id`,`status`),
  KEY `trs_teacher_status_idx` (`teacher_user_id`,`status`),
  KEY `trs_class_subject_idx` (`school_class_id`,`subject_id`),
  KEY `trs_session_term_idx` (`academic_session_id`,`term_id`),
  CONSTRAINT `teacher_result_submissions_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_result_submissions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_result_submissions_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_result_submissions_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_result_submissions_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_result_submissions_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_result_submissions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_result_submissions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_result_submissions_teacher_user_id_foreign` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_result_submissions_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_result_submissions`
--

LOCK TABLES `teacher_result_submissions` WRITE;
/*!40000 ALTER TABLE `teacher_result_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_result_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subject_assignments`
--

DROP TABLE IF EXISTS `teacher_subject_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_subject_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `teacher_user_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `school_class_id` bigint unsigned DEFAULT NULL,
  `academic_session_id` bigint unsigned DEFAULT NULL,
  `term_id` bigint unsigned DEFAULT NULL,
  `role_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'subject_teacher',
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_subject_assignments_term_id_foreign` (`term_id`),
  KEY `teacher_subject_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `tsa_school_status_idx` (`school_id`,`status`),
  KEY `tsa_teacher_status_idx` (`teacher_user_id`,`status`),
  KEY `tsa_subject_status_idx` (`subject_id`,`status`),
  KEY `tsa_class_status_idx` (`school_class_id`,`status`),
  KEY `tsa_school_teacher_subject_idx` (`school_id`,`teacher_user_id`,`status`,`subject_id`),
  KEY `tsa_school_subject_class_idx` (`school_id`,`subject_id`,`school_class_id`,`status`),
  KEY `tsa_context_status_idx` (`academic_session_id`,`term_id`,`status`),
  CONSTRAINT `teacher_subject_assignments_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_subject_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_subject_assignments_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teacher_subject_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_assignments_teacher_user_id_foreign` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_assignments_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subject_assignments`
--

LOCK TABLES `teacher_subject_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_subject_assignments` DISABLE KEYS */;
INSERT INTO `teacher_subject_assignments` VALUES (1,1,2,1,1,NULL,NULL,'subject_teacher',NULL,NULL,'active',2,NULL,'2026-05-15 07:20:33','2026-05-15 07:20:33',NULL);
/*!40000 ALTER TABLE `teacher_subject_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `academic_session_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terms_school_id_academic_session_id_name_unique` (`school_id`,`academic_session_id`,`name`),
  KEY `terms_academic_session_id_foreign` (`academic_session_id`),
  CONSTRAINT `terms_academic_session_id_foreign` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `terms_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (1,1,1,'First Term','2026-05-01','2026-07-31',1,'active','2026-05-15 07:04:27','2026-05-15 07:04:27',NULL);
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_school_roles`
--

DROP TABLE IF EXISTS `user_school_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_school_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_school_role_unq` (`user_id`,`school_id`,`role_name`),
  KEY `user_school_roles_assigned_by_foreign` (`assigned_by`),
  KEY `usr_user_status_idx` (`user_id`,`status`),
  KEY `usr_school_role_idx` (`school_id`,`role_name`),
  CONSTRAINT `user_school_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_school_roles_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_school_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_school_roles`
--

LOCK TABLES `user_school_roles` WRITE;
/*!40000 ALTER TABLE `user_school_roles` DISABLE KEYS */;
INSERT INTO `user_school_roles` VALUES (1,2,1,'school_admin','active',1,NULL,'2026-05-15 06:54:32','2026-05-15 06:54:32'),(2,2,1,'teacher','active',1,NULL,'2026-05-15 06:58:50','2026-05-15 06:58:50'),(3,2,1,'result_officer','active',1,NULL,'2026-05-15 06:59:44','2026-05-15 06:59:44');
/*!40000 ALTER TABLE `user_school_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_staff_code_unique` (`staff_code`),
  KEY `users_school_id_foreign` (`school_id`),
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Super Admin','admin@sanfaani.test',NULL,NULL,'$2y$12$fiuUgQjpMLAcx1y4DC6Ur.NCqBAhoaxGLvihxWHIcWGWImHIqsWXK',0,NULL,'2026-05-15 06:39:25','2026-05-15 06:39:25'),(2,1,'sanfaani','salihutaofeekoriyomi70@gmail.com','S/TCH/2026/001',NULL,'$2y$12$8MfhbuDdPcHoqJvjHBylHe93iBQnqc3mM67dVg5Ch8dbkZfYFUNqu',0,NULL,'2026-05-15 06:54:32','2026-05-15 06:58:50');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-15 14:34:15
