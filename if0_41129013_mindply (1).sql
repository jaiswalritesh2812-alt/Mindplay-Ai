-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Generation Time: Feb 27, 2026 at 12:07 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41129013_mindply`
--

-- --------------------------------------------------------

--
-- Table structure for table `custom_topic_summaries`
--

CREATE TABLE `custom_topic_summaries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `mindmap` text NOT NULL,
  `summary` text NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `custom_topic_summaries`
--

INSERT INTO `custom_topic_summaries` (`id`, `user_id`, `topic`, `mindmap`, `summary`, `generated_at`) VALUES
(7, 9, 'block storage', 'mindmap\n  root((Block Storage))\n    Fundamentals\n      Data Organization\n      Volume Blocks\n      Raw Storage\n      No Hierarchy\n    Characteristics\n      Performance\n      Low Latency\n      Predictable IOPS\n      Large Block Size\n    Storage Types\n      HDD-backed\n      SSD-backed\n      Provisioned IOPS\n      General Purpose\n    Use Cases\n      Databases\n      Virtual Machines\n      Boot Volumes\n      High I/O Apps\n    Management\n      Snapshots\n      Encryption\n      Volume Types\n      Attach/Detach', '<h3>Brief Overview</h3>\n<p>Block storage is a type of data storage that organizes data into fixed-size chunks called blocks, each with a unique address. Unlike file storage that uses a hierarchical structure, block storage provides raw storage volumes that can be attached to computing instances. This storage method is commonly used for databases, virtual machines, and applications requiring high-performance, low-latency access to data.</p>\n\n<h3>Key Concepts</h3>\n<ul>\n  <li><strong>Blocks</strong>: Fixed-size units of data (typically 4KB to 64KB) with unique identifiers</li>\n  <li><strong>Volume</strong>: A raw storage device that can be attached to computing instances</li>\n  <li><strong>Volume Attachment</strong>: Connecting a block storage volume to a server or virtual machine</li>\n  <li><strong>File System</strong>: Must be created on block storage before use for file organization</li>\n  <li><strong>IOPS</strong>: Input/Output Operations Per Second, a key performance metric</li>\n  <li><strong>Provisioned IOPS</strong>: Guaranteed performance levels for demanding workloads</li>\n  <li><strong>Encryption</strong>: Security measure at rest and in transit for block storage volumes</li>\n</ul>\n\n<h3>Detailed Explanation</h3>\n<p>Block storage works by dividing data into equal-sized blocks that are stored independently on storage devices. Each block is assigned a unique address, allowing direct access without navigating through a directory structure. When an application requests data, the operating system translates the file request into specific block addresses, enabling rapid retrieval. This direct addressability makes block storage exceptionally fast for random read/write operations.</p>\n\n<p>The architecture of block storage is fundamentally different from file storage. While file storage uses a tree-like hierarchy of folders and files, block storage operates at a lower level, providing raw storage capacity. This means the storage system doesn\'t understand files or directories - it simply manages blocks of data. The intelligence of organizing these blocks into files and folders is handled by the operating system or database management system using a file system or database engine.</p>\n\n<p>Performance in block storage is typically measured by IOPS (Input/Output Operations Per Second) and throughput. Different storage types offer varying performance characteristics: HDD-backed volumes provide cost-effective storage for less demanding workloads, while SSD-backed volumes deliver high IOPS and low latency for performance-critical applications. Some cloud providers offer provisioned IOPS volumes that guarantee specific performance levels, ideal for databases and enterprise applications with strict performance requirements.</p>\n\n<p>Management of block storage involves several key operations. Volumes can be created, resized, and deleted independently of computing instances. Snapshots provide point-in-time backups for disaster recovery. Encryption ensures data security both at rest and during transfer. The ability to detach volumes from one instance and attach them to another provides flexibility in workload management and migration scenarios.</p>\n\n<h3>Important Points to Remember</h3>\n<ul>\n  <li>Block storage provides raw storage volumes without inherent file system structure</li>\n  <li>Each block has a unique address for direct, low-latency access</li>\n  <li>Must create a file system on block storage before using it for files</li>\n  <li>Performance is measured by IOPS and throughput, not by file operations</li>\n  <li>Excellent for structured data and random access patterns like databases</li>\n  <li>Can be easily scaled, backed up with snapshots, and migrated between instances</li>\n</ul>\n\n<h3>Real-World Applications or Examples</h3>\n<ul>\n  <li><strong>Relational Databases</strong>: MySQL, PostgreSQL, and Oracle databases running on block volumes for transaction processing</li>\n  <li><strong>Virtual Machine Boot Disks</strong>: Operating system drives for VMs in cloud environments like AWS EC2 or Azure VMs</li>\n  <li><strong>Enterprise Applications</strong>: SAP, Exchange Server, and other business-critical applications requiring consistent performance</li>\n  <li><strong>Container Storage</strong>: Persistent volumes for stateful containerized applications in Kubernetes clusters</li>\n</ul>\n\n<h3>Study Tips</h3>\n<ul>\n  <li><strong>Compare Storage Types</strong>: Create a comparison table of block vs file vs object storage focusing on structure, performance, and use cases</li>\n  <li><strong>Use Hands-On Practice</strong>: Set up a block volume in a cloud provider\'s free tier to understand creation, attachment, and file system initialization</li>\n  <li><strong>Monitor Performance Metrics</strong>: Practice reading IOPS, throughput, and latency metrics to understand performance characteristics of different volume types</li>\n</ul>', '2026-02-19 19:11:12'),
(8, 12, 'linux', 'mindmap\n  root((Linux))\n    Core System Concepts\n      Kernel\n      Shell\n      Filesystem Hierarchy\n      Processes and Daemons\n    Command Line Interface (CLI)\n      Basic Commands\n      File Manipulation\n      Permissions and Ownership\n      Piping and Redirection\n    System Administration\n      User Management\n      Package Management\n      Service Management\n      Networking\n    Distributions and Environments\n      Debian/Ubuntu Family\n      Red Hat Family\n      Arch Linux\n      Desktop Environments', '<h3>Brief Overview</h3>\n<p>Linux is an open-source, Unix-like operating system kernel originally created by Linus Torvalds in 1991. It serves as the core component that manages hardware resources and allows software applications to run efficiently. Today, Linux powers everything from smartphones and supercomputers to web servers and cloud infrastructure.</p>\n\n<h3>Key Concepts</h3>\n<ul>\n  <li><strong>The Kernel:</strong> The core part of the OS that handles memory, processes, and hardware.</li>\n  <li><strong>The Shell:</strong> A command-line interface (like Bash) that interprets user commands and communicates with the kernel.</li>\n  <li><strong>Filesystem Hierarchy Standard (FHS):</strong> A structure defining directories like /home, /etc, and /var.</li>\n  <li><strong>Permissions:</strong> A system determining who can read, write, or execute files (read/write/execute bits).</li>\n  <li><strong>Package Management:</strong> Tools (like apt or dnf) used to install, update, and remove software.</li>\n  <li><strong>Daemons:</strong> Background processes that run services (e.g., web servers, schedulers).</li>\n  <li><strong>Distributions:</strong> Variations of Linux bundling the kernel with different software packages (e.g., Ubuntu, Fedora).</li>\n</ul>\n\n<h3>Detailed Explanation</h3>\n<p>At the heart of Linux is the <strong>kernel</strong>, which acts as a bridge between software and hardware. When a program needs to access a resource (like writing to a disk or sending data over a network), it sends a request to the kernel. The kernel processes these requests and manages the hardware to ensure stability and security. Unlike many proprietary operating systems, the Linux kernel is freely available and modifiable, allowing developers worldwide to contribute to its improvement.</p>\n\n<p>The <strong>Shell</strong> is the user\'s primary method of interacting with the system in a non-graphical environment. The most common shell, Bash (Bourne Again SHell), allows users to execute commands, automate tasks via scripts, and control the system with high precision. The command line is powerful because it can be chained together using pipes and redirection, allowing complex data processing with simple commands.</p>\n\n<p>Linux adheres to the <strong>Filesystem Hierarchy Standard (FHS)</strong>, which organizes files into a consistent structure. Unlike Windows, which uses drive letters (C:), Linux uses a single root directory (\"/\"). System configurations are typically stored in <code>/etc</code>, user data in <code>/home</code>, and temporary files in <code>/tmp</code>. This standardization makes it easier to manage servers and write portable scripts.</p>\n\n<p>Security in Linux is handled through strict <strong>file permissions</strong> and user accounts. Every file has an owner and a group, and permissions are defined for reading (r), writing (w), and executing (x). This granular control prevents unauthorized access. Additionally, the concept of the \"superuser\" (root) allows for administrative tasks, but only when explicitly granted, reducing the risk of accidental system-wide changes.</p>\n\n<h3>Important Points to Remember</h3>\n<ul>\n  <li>Linux is <strong>case-sensitive</strong>; \"File.txt\" and \"file.txt\" are different.</li>\n  <li>The <strong>root user</strong> has absolute power; use <code>sudo</code> carefully.</li>\n  <li>Everything in Linux is treated as a <strong>file</strong>, including hardware devices.</li>\n  <li>There is no \"trash\" in the command line; deleted files are gone immediately.</li>\n  <li>Learning to read <strong>man pages</strong> (manuals) is essential for self-sufficiency.</li>\n</ul>\n\n<h3>Real-World Applications or Examples</h3>\n<ul>\n  <li><strong>Web Servers:</strong> The majority of the internet (including Google and Facebook) runs on Linux servers due to their stability and low cost.</li>\n  <li><strong>Embedded Systems:</strong> Linux is used in routers, smart TVs, and IoT devices because of its flexibility and small footprint.</li>\n  <li><strong>Cloud Computing:</strong> Platforms like Amazon AWS and Google Cloud rely heavily on Linux virtual machines.</li>\n  <li><strong>Software Development:</strong> Many developers prefer Linux for its powerful command-line tools and native support for programming languages.</li>\n</ul>\n\n<h3>Study Tips</h3>\n<ul>\n  <li><strong>Use a Virtual Machine:</strong> Install a distribution like Ubuntu in VirtualBox to experiment safely without risking your main OS.</li>\n  <li><strong>Practice Daily:</strong> Try to perform basic tasks (like moving files or editing text) solely through the command line.</li>\n  <li><strong>Break Things (Safely):</strong> Learning to fix broken permissions or configuration files is one of the best ways to understand how Linux works.</li>\n</ul>', '2026-02-26 19:19:46');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tracks all user login attempts';

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `login_time`, `ip_address`, `user_agent`) VALUES
(6, 6, '2026-02-11 08:31:19', '106.193.204.132', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(9, 6, '2026-02-11 08:55:07', '103.233.94.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(11, 6, '2026-02-11 14:21:30', '106.193.220.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(12, 6, '2026-02-20 03:05:59', '103.233.94.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(13, 9, '2026-02-20 03:09:46', '103.233.94.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(14, 6, '2026-02-25 06:23:15', '106.193.226.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0'),
(15, 10, '2026-02-25 06:34:48', '106.193.226.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0'),
(16, 6, '2026-02-25 09:02:20', '106.193.226.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0'),
(17, 11, '2026-02-25 09:07:01', '106.193.226.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(18, 6, '2026-02-27 03:08:10', '152.58.44.180', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(19, 6, '2026-02-27 03:11:03', '152.58.43.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(20, 12, '2026-02-27 03:14:21', '152.58.43.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `syllabus_id` int(11) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `syllabus_id`, `question`, `answer`) VALUES
(131, 12, 'Q: How does block storage present data to the virtual machine?\nA) As a hierarchical folder structure managed by the cloud provider\nB) As a collection of files stored in object buckets\nC) As individual blocks that act like physical hard drives\nD) As a stream of unstructured data packets', 'C'),
(132, 12, 'Q: Which of the following responsibilities falls on the user after attaching a block volume to a virtual server?\nA) Creating the virtual disks\nB) Formatting and managing the storage (e.g., using NTFS or ext4)\nC) Physically plugging in the hard drive\nD) Dividing the data into blocks', 'B'),
(133, 12, 'Q: Which characteristic of block storage makes it suitable for high-performance workloads like databases?\nA) High latency and low performance\nB) High performance and low latency\nC) Managing file systems automatically\nD) Storing data in unstructured buckets', 'B'),
(134, 12, 'Q: According to the content, which of the following is a common use case for block storage?\nA) Storing static website images\nB) Virtual machine boot drives\nC) Archiving old email logs\nD) File sharing between multiple servers simultaneously', 'B'),
(135, 12, 'Q: Which of the following services is an example of cloud block storage?\nA) Amazon S3\nB) Azure Blob Storage\nC) Google Cloud Storage\nD) Amazon Elastic Block Store (AWS EBS)', 'D'),
(136, 10, 'Q: What is a key advantage of using cloud platforms?\nA) Increased hardware maintenance\nB) Scalability and cost efficiency\nC) Limited internet access\nD) Reduced virtual machine availability', 'B'),
(137, 10, 'Q: Which of the following is an example of a major cloud provider?\nA) Microsoft Excel\nB) Google Cloud Platform\nC) Facebook\nD) Mozilla Firefox', 'B'),
(138, 10, 'Q: Which cloud service model provides virtualized computing resources like servers and storage?\nA) Software as a Service (SaaS)\nB) Platform as a Service (PaaS)\nC) Infrastructure as a Service (IaaS)\nD) On-premises as a Service (OPaaS)', 'C'),
(139, 10, 'Q: Which deployment model combines on-premises infrastructure with cloud services?\nA) Public cloud\nB) Private cloud\nC) Hybrid cloud\nD) Community cloud', 'C'),
(140, 10, 'Q: What is a common challenge associated with cloud computing?\nA) Lack of internet connectivity requirements\nB) Security and privacy challenges\nC) Inability to scale resources\nD) Increased physical storage requirements', 'B'),
(146, 13, 'Q: What is the primary focus of Web Application Hacking?\nA) Developing new web applications and software features\nB) Identifying and fixing security vulnerabilities in websites and web apps\nC) Increasing website traffic and search engine optimization\nD) Designing user interfaces and improving user experience', 'B'),
(147, 13, 'Q: Why is proper authorization specifically mentioned in the context of Web Application Hacking?\nA) Because hacking requires no permission to be effective\nB) To emphasize that ethical hacking must be conducted legally\nC) Because it makes the hacking process faster and easier\nD) To allow hackers to test any website without consequences', 'B'),
(148, 13, 'Q: Why is Web Application Hacking considered a major area in cybersecurity?\nA) Because most businesses rely on online platforms\nB) Because web applications are becoming obsolete\nC) Because desktop software is no longer used\nD) Because social media is the only relevant platform', 'A'),
(149, 13, 'Q: Which of the following best describes the goal of Web Application Hacking?\nA) To shut down competitor websites permanently\nB) To exploit vulnerabilities for personal gain\nC) To find and resolve security flaws in web apps\nD) To steal data from websites without being detected', 'C'),
(150, 13, 'Q: What is the relationship between Web Application Hacking and cybersecurity?\nA) It is a minor, unrelated field\nB) It is a major area within the discipline\nC) It is an outdated practice no longer used\nD) It is only relevant for government agencies', 'B'),
(151, 13, 'Q: Which type of platforms are the primary targets for Web Application Hacking?\nA) Desktop applications and mobile operating systems\nB) Websites and web applications\nC) Hardware devices and IoT sensors\nD) Cloud storage and network servers', 'B'),
(152, 13, 'Q: According to the content, what drives the importance of Web Application Hacking in business?\nA) The decline of e-commerce and online services\nB) The shift back to traditional brick-and-mortar stores\nC) The fact that most businesses run online platforms\nD) The reduced need for cybersecurity measures', 'C'),
(153, 13, 'Q: What is the difference between Web Application Hacking and malicious hacking?\nA) There is no difference; both are illegal\nB) Web Application Hacking focuses on fixing issues, malicious hacking exploits them\nC) Malicious hacking is always done with proper authorization\nD) Web Application Hacking is only about creating malware', 'B'),
(154, 13, 'Q: Which of the following is NOT a characteristic of Web Application Hacking as described?\nA) It involves identifying vulnerabilities\nB) It requires proper authorization\nC) It focuses on fixing security issues\nD) It primarily targets physical security systems', 'D'),
(155, 13, 'Q: Based on the content, what is the broader significance of Web Application Hacking?\nA) It helps maintain the security of online business platforms\nB) It allows for the creation of new web applications\nC) It replaces the need for network security\nD) It is mainly used for academic research only', 'A'),
(156, 14, 'Q: Which principle of the CIA Triad ensures that information is only accessed by authorized individuals?\nA) Availability\nB) Integrity\nC) Confidentiality\nD) Authentication', 'C'),
(157, 14, 'Q: According to the provided text, which principle of the CIA Triad is responsible for keeping websites and servers running without interruptions?\nA) Integrity\nB) Availability\nC) Confidentiality\nD) Resilience', 'B'),
(158, 14, 'Q: Using passwords or encryption to protect private data is an example of which CIA Triad principle?\nA) Availability\nB) Integrity\nC) Accountability\nD) Confidentiality', 'D'),
(159, 14, 'Q: Which principle of the CIA Triad ensures that information remains accurate, complete, and unaltered?\nA) Integrity\nB) Availability\nC) Confidentiality\nD) Non-repudiation', 'A'),
(160, 14, 'Q: Preventing unauthorized changes to exam results or financial records is an example of which CIA Triad principle?\nA) Confidentiality\nB) Availability\nC) Integrity\nD) Access Control', 'C');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `user_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `question_id`, `user_answer`, `is_correct`, `attempted_at`) VALUES
(122, 9, 135, 'D', 1, '2026-02-20 03:10:25'),
(123, 9, 132, 'A', 0, '2026-02-20 03:10:25'),
(124, 9, 133, 'C', 0, '2026-02-20 03:10:25'),
(125, 9, 134, 'C', 0, '2026-02-20 03:10:25'),
(126, 9, 131, 'B', 0, '2026-02-20 03:10:25'),
(132, 10, 137, 'B', 1, '2026-02-25 07:17:56'),
(133, 10, 139, 'B', 0, '2026-02-25 07:17:56'),
(134, 10, 136, 'C', 0, '2026-02-25 07:17:56'),
(135, 10, 140, 'B', 1, '2026-02-25 07:17:56'),
(136, 10, 138, 'A', 0, '2026-02-25 07:17:56'),
(137, 12, 158, 'D', 1, '2026-02-27 03:16:37'),
(138, 12, 157, 'B', 1, '2026-02-27 03:16:37'),
(139, 12, 159, 'A', 1, '2026-02-27 03:16:37'),
(140, 12, 160, 'A', 0, '2026-02-27 03:16:37'),
(141, 12, 156, 'C', 1, '2026-02-27 03:16:37'),
(142, 12, 149, 'B', 0, '2026-02-27 03:23:59'),
(143, 12, 147, 'D', 0, '2026-02-27 03:23:59'),
(144, 12, 151, 'B', 1, '2026-02-27 03:23:59'),
(145, 12, 150, 'B', 1, '2026-02-27 03:23:59'),
(146, 12, 148, 'A', 1, '2026-02-27 03:23:59'),
(147, 12, 155, 'A', 1, '2026-02-27 03:23:59'),
(148, 12, 153, 'A', 0, '2026-02-27 03:23:59'),
(149, 12, 146, 'B', 1, '2026-02-27 03:23:59'),
(150, 12, 154, 'B', 0, '2026-02-27 03:23:59'),
(151, 12, 152, 'D', 0, '2026-02-27 03:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `time_taken` int(11) DEFAULT 0 COMMENT 'Time taken in seconds',
  `syllabus_id` int(11) DEFAULT NULL COMMENT 'Syllabus/topic for this quiz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `user_id`, `subject_id`, `score`, `created_at`, `time_taken`, `syllabus_id`) VALUES
(16, 9, 10, 20, '2026-02-20 03:10:25', 32, 12),
(17, 10, 12, 80, '2026-02-25 06:35:34', 38, 13),
(18, 10, 10, 40, '2026-02-25 07:17:56', 21, 10),
(19, 12, 13, 80, '2026-02-27 03:16:37', 117, 14),
(20, 12, 12, 50, '2026-02-27 03:23:59', 193, 13);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL COMMENT 'Subject description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `description`) VALUES
(10, 'cloud computing', NULL),
(12, 'ETHICAL HACKING', NULL),
(13, 'Information Security', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `syllabus`
--

CREATE TABLE `syllabus` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `topic` varchar(150) DEFAULT NULL,
  `content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `syllabus`
--

INSERT INTO `syllabus` (`id`, `subject_id`, `topic`, `content`) VALUES
(10, 10, 'cloud platform', 'Cloud platforms provide on-demand computing resources such as virtual machines, storage, and networking over the internet. Major cloud providers include AWS, Microsoft Azure, and Google Cloud Platform. Cloud services are categorized into IaaS, PaaS, and SaaS. Deployment models include public, private, and hybrid cloud. Cloud platforms offer scalability and cost efficiency but also face security and privacy challenges.'),
(12, 10, 'block storage', 'What it is:\r\nBlock storage divides data into blocks and stores them separately. These blocks can be attached to virtual machines like physical hard drives.\r\n\r\nHow it works:\r\nA cloud provider creates virtual disks (block volumes) that you attach to a virtual server. The server formats and manages the storage (e.g., using file systems like NTFS or ext4).\r\n\r\nKey Features:\r\n\r\nHigh performance and low latency\r\n\r\nSuitable for databases and enterprise applications\r\n\r\nScalable (can increase size as needed)\r\n\r\nProvides raw storage (user manages file system)\r\n\r\nCommon Use Cases:\r\n\r\nDatabases (e.g., MySQL, Oracle)\r\n\r\nVirtual machine boot drives\r\n\r\nTransactional applications\r\n\r\nHigh-performance workloads\r\n\r\nExamples of Cloud Block Storage Services:\r\n\r\nAmazon Elastic Block Store (AWS EBS)\r\n\r\nAzure Managed Disks\r\n\r\nGoogle Persistent Disk'),
(13, 12, 'Web Application Hacking', 'Web Application Hacking focuses on identifying and fixing security vulnerabilities in websites and web apps with proper authorization. It is a major area in cybersecurity because most businesses run online platforms.'),
(14, 13, 'CIA', 'The CIA Triad is a model that describes the three core principles of information security:\r\n\r\nConfidentiality\r\nEnsures that information is only accessed by authorized individuals.\r\nExample: Using passwords or encryption to protect private data.\r\n\r\nIntegrity\r\nEnsures that information remains accurate, complete, and unaltered.\r\nExample: Preventing unauthorized changes to exam results or financial records.\r\n\r\nAvailability\r\nEnsures that information and systems are accessible when needed.\r\nExample: Keeping websites and servers running without interruptions.');

-- --------------------------------------------------------

--
-- Table structure for table `topic_summaries`
--

CREATE TABLE `topic_summaries` (
  `id` int(11) NOT NULL,
  `syllabus_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `summary` text NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `topic_summaries`
--

INSERT INTO `topic_summaries` (`id`, `syllabus_id`, `user_id`, `summary`, `generated_at`) VALUES
(9, 10, 11, '```html\n<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Cloud Platform Summary</title>\n    <style>\n        body {\n            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;\n            line-height: 1.6;\n            color: #333;\n            max-width: 800px;\n            margin: 0 auto;\n            padding: 20px;\n            background-color: #f8f9fa;\n        }\n        h3 {\n            color: #0066cc;\n            border-bottom: 2px solid #0066cc;\n            padding-bottom: 5px;\n            margin-top: 25px;\n        }\n        p {\n            background-color: #ffffff;\n            padding: 15px;\n            border-radius: 8px;\n            box-shadow: 0 2px 4px rgba(0,0,0,0.1);\n            margin-bottom: 10px;\n        }\n        ul {\n            background-color: #ffffff;\n            padding: 20px 40px;\n            border-radius: 8px;\n            box-shadow: 0 2px 4px rgba(0,0,0,0.1);\n            margin-bottom: 10px;\n        }\n        li {\n            margin-bottom: 8px;\n            padding-left: 5px;\n        }\n        strong {\n            color: #0066cc;\n        }\n    </style>\n</head>\n<body>\n\n    <h3>Overview</h3>\n    <p>Cloud platforms deliver on-demand computing resources like virtual machines, storage, and networking over the internet, eliminating the need for physical infrastructure. Major providers include AWS, Azure, and Google Cloud Platform, which offer services categorized into IaaS, PaaS, and SaaS. While these platforms provide excellent scalability and cost efficiency, they also present unique security and privacy challenges.</p>\n\n    <h3>Key Concepts</h3>\n    <ul>\n        <li><strong>Major Providers:</strong> The \"Big Three\" are AWS (Amazon Web Services), Microsoft Azure, and Google Cloud Platform (GCP).</li>\n        <li><strong>Service Models (IaaS):</strong> Infrastructure as a Service provides fundamental computing resources like virtual machines and storage (e.g., AWS EC2).</li>\n        <li><strong>Service Models (PaaS):</strong> Platform as a Service offers hardware and software tools for application development (e.g., Google App Engine).</li>\n        <li><strong>Service Models (SaaS):</strong> Software as a Service delivers ready-to-use applications over the internet (e.g., Gmail, Salesforce).</li>\n        <li><strong>Deployment (Public):</strong> Resources are owned by a third-party provider and shared among multiple organizations via the internet.</li>\n        <li><strong>Deployment (Private/Hybrid):</strong> Private clouds are used exclusively by one organization, while Hybrid clouds combine public and private environments.</li>\n    </ul>\n\n    <h3>Important Points to Remember</h3>\n    <ul>\n        <li><strong>Pay-as-you-go:</strong> You typically pay only for the resources you consume, rather than buying hardware upfront.</li>\n        <li><strong>Elastic Scalability:</strong> Cloud platforms can automatically scale resources up or down based on user demand.</li>\n        <li><strong>Shared Responsibility:</strong> Cloud security is a partnership; the provider secures the infrastructure, but the customer must secure their data and access.</li>\n        <li><strong>Internet Dependency:</strong> Accessing cloud resources requires a reliable internet connection.</li>\n        <li><strong>Vendor Lock-in:</strong> Moving data or applications from one cloud provider to another can be complex and expensive.</li>\n    </ul>\n\n    <h3>Study Tips</h3>\n    <ul>\n        <li><strong>Use Acronyms:</strong> Memorize the service models with the acronym \"SPI\" (SaaS, PaaS, IaaS) to remember the order from software down to infrastructure.</li>\n        <li><strong>Real-World Analogies:</strong> Think of IaaS as renting a plot of land to build your own house, PaaS as renting a pre-built house where you just add furniture, and SaaS as staying in a hotel where everything is provided for you.</li>\n        <li><strong>Visualize Deployment:</strong> Draw a Venn diagram separating Public, Private, and Hybrid clouds to visualize their relationships and differences.</li>\n    </ul>\n\n</body>\n</html>\n```', '2026-02-25 01:07:38'),
(10, 13, 11, '<h3 style=\"color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:5px;\">Overview</h3>\n<p style=\"font-family: Arial, sans-serif; line-height: 1.6;\">Web application hacking is an authorized, ethical practice of finding and fixing security flaws in websites and online platforms. Since most businesses depend on web apps, this discipline is critical for protecting user data and ensuring operational integrity. Ethical hacking follows strict guidelines and rules of engagement to improve security rather than exploit it.</p>\n\n<h3 style=\"color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:5px;\">Key Concepts</h3>\n<ul style=\"font-family: Arial, sans-serif; line-height: 1.6;\">\n    <li><strong>Authorization and Scope:</strong> Testing is only performed with explicit permission and within defined boundaries.</li>\n    <li><strong>Common Vulnerabilities:</strong> Recognizing issues like SQL Injection (SQLi), Cross-Site Scripting (XSS), and Cross-Site Request Forgery (CSRF).</li>\n    <li><strong>Reconnaissance:</strong> Gathering information about the target application to understand its structure and functionality.</li>\n    <li><strong>Testing Methodologies:</strong> Using structured approaches (e.g., OWASP Top 10) to assess security systematically.</li>\n    <li><strong>Tools of the Trade:</strong> Leveraging tools like Burp Suite, OWASP ZAP, and Nmap for scanning and analysis.</li>\n    <li><strong>Reporting and Remediation:</strong> Documenting findings and providing actionable recommendations to developers.</li>\n</ul>\n\n<h3 style=\"color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:5px;\">Important Points to Remember</h3>\n<ul style=\"font-family: Arial, sans-serif; line-height: 1.6;\">\n    <li>Always obtain <strong>clear, written permission</strong> before any testing—unauthorized hacking is illegal.</li>\n    <li>Focus on <strong>defensive outcomes</strong>—the goal is to strengthen security, not cause harm.</li>\n    <li>Stay updated with the <strong>OWASP Top 10</strong> list, as it reflects the most critical web application security risks.</li>\n    <li>Use a <strong>controlled environment</strong> (like a lab or sandbox) when practicing to avoid unintended impact.</li>\n</ul>\n\n<h3 style=\"color:#2c3e50; border-bottom:2px solid #3498db; padding-bottom:5px;\">Study Tips</h3>\n<ul style=\"font-family: Arial, sans-serif; line-height: 1.6;\">\n    <li>Practice in safe, legal environments like <strong>OWASP Juice Shop</strong>, <strong>TryHackMe</strong>, or <strong>PortSwigger Web Security Academy</strong>.</li>\n    <li>Learn by <strong>doing</strong>: set up a local web app (e.g., using Docker) and run basic vulnerability scans with tools like Burp Suite Community Edition.</li>\n    <li>Review case studies and <strong>real-world scenarios</strong> to understand how vulnerabilities are exploited and fixed.</li>\n</ul>', '2026-02-25 01:08:10'),
(11, 14, 12, '```html\n<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Information Security - CIA Triad Summary</title>\n    <style>\n        body {\n            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;\n            line-height: 1.6;\n            color: #333;\n            max-width: 800px;\n            margin: 0 auto;\n            padding: 20px;\n            background-color: #f9f9f9;\n        }\n        h3 {\n            color: #2c3e50;\n            border-bottom: 2px solid #3498db;\n            padding-bottom: 8px;\n            margin-top: 25px;\n        }\n        ul {\n            background: #fff;\n            padding: 20px 40px;\n            border-radius: 8px;\n            box-shadow: 0 2px 4px rgba(0,0,0,0.1);\n        }\n        li {\n            margin-bottom: 10px;\n            padding-left: 5px;\n        }\n        p {\n            background: #fff;\n            padding: 15px 20px;\n            border-radius: 8px;\n            box-shadow: 0 2px 4px rgba(0,0,0,0.1);\n        }\n        strong {\n            color: #2980b9;\n        }\n    </style>\n</head>\n<body>\n\n    <h3>Overview</h3>\n    <p>The CIA Triad is a foundational model in information security that outlines the three core principles: Confidentiality, Integrity, and Availability. Together, these principles guide the protection of data and systems against unauthorized access, tampering, and downtime. Understanding the CIA Triad is essential for implementing effective security measures in any organization.</p>\n\n    <h3>Key Concepts</h3>\n    <ul>\n        <li><strong>Confidentiality:</strong> Ensures that information is accessible only to authorized individuals, preventing unauthorized disclosure. Example: Using passwords or encryption to protect private data.</li>\n        <li><strong>Integrity:</strong> Ensures that information remains accurate, complete, and unaltered by unauthorized parties. Example: Preventing unauthorized changes to exam results or financial records.</li>\n        <li><strong>Availability:</strong> Ensures that information and systems are accessible and usable when needed by authorized users. Example: Keeping websites and servers running without interruptions.</li>\n        <li><strong>Balancing the Triad:</strong> Security measures must balance all three principles; overemphasizing one (e.g., availability) can weaken others (e.g., confidentiality).</li>\n        <li><strong>Application Across Domains:</strong> The CIA Triad applies to various areas like network security, data protection, and system administration.</li>\n    </ul>\n\n    <h3>Important Points to Remember</h3>\n    <ul>\n        <li>The CIA Triad is a core framework used in cybersecurity certifications like CISSP and CompTIA Security+.</li>\n        <li>Violations of the CIA Triad can lead to data breaches, financial losses, and reputational damage.</li>\n        <li>Real-world incidents often involve a compromise of multiple CIA principles (e.g., ransomware attacks affect both availability and integrity).</li>\n        <li>It\'s a model, not a solution; specific tools and policies are needed to enforce each principle.</li>\n    </ul>\n\n    <h3>Study Tips</h3>\n    <ul>\n        <li>Use acronyms: Remember CIA as the \"security triangle\" to recall Confidentiality, Integrity, and Availability.</li>\n        <li>Apply to scenarios: Think of real-life examples like online banking (confidentiality) or social media (integrity) to relate concepts to everyday use.</li>\n        <li>Create flashcards: List each principle with its definition and an example to reinforce memory through active recall.</li>\n    </ul>\n\n</body>\n</html>\n```', '2026-02-26 19:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','student') DEFAULT 'student',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Last login timestamp',
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Account creation timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `last_login`, `created_at`) VALUES
(6, 'Admin', 'admin@mindplay.com', '$2y$12$eXNv8btSA6yG2jeE7gKdt.m/WEMLkDVqoSseZISVKRNV2kL0nFOke', 'admin', '2026-02-27 03:11:03', '2026-02-11 06:38:06'),
(9, 'shivam yadav', 'shivam@a.in', '$2y$10$a6/4JLeJyrRnCimC69lfDuvIatz2H1y4aXlCbW97schxl.3xANNpi', 'student', '2026-02-20 03:09:46', '2026-02-20 03:09:28'),
(10, 'RITESH JAISWAL', 'jairitesh2824@gmail.com', '$2y$10$0Ims1ynSjndOpeklVjt9z.sKQktVfAbYJPmhXo9Jqmq0j4I9aZ9F.', 'student', '2026-02-25 06:34:48', '2026-02-25 06:34:26'),
(11, 'suhail ansari', 'hjdjbwej@22584.com', '$2y$10$Q9rQg6twejPcTDsdjuaMdOQpmBJmzz8facV7Zf7EKYMgFT/LdECjW', 'student', '2026-02-25 09:07:01', '2026-02-25 09:06:30'),
(12, 'shivansh', 'shivansh@a.in', '$2y$10$xRsba39GVz2s7fnu2UEJkOyrCjeAnicd5CrSUoM22vIPoVNeHQFuy', 'student', '2026-02-27 03:14:21', '2026-02-27 03:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `weak_topics`
--

CREATE TABLE `weak_topics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `syllabus_id` int(11) DEFAULT NULL,
  `mistake_count` int(11) DEFAULT 1,
  `last_attempt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `weak_topics`
--

INSERT INTO `weak_topics` (`id`, `user_id`, `syllabus_id`, `mistake_count`, `last_attempt`) VALUES
(14, 9, 12, 4, '2026-02-20 03:10:25'),
(15, 10, 13, 1, '2026-02-25 06:35:34'),
(16, 10, 10, 3, '2026-02-25 07:17:56'),
(17, 12, 14, 1, '2026-02-27 03:16:37'),
(18, 12, 13, 5, '2026-02-27 03:23:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `custom_topic_summaries`
--
ALTER TABLE `custom_topic_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_topic` (`user_id`,`topic`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `topic` (`topic`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_login` (`user_id`,`DESC`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_id` (`syllabus_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_results_leaderboard` (`subject_id`,`DESC`,`time_taken`),
  ADD KEY `idx_results_topic_leaderboard` (`syllabus_id`,`DESC`,`time_taken`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `syllabus`
--
ALTER TABLE `syllabus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `topic_summaries`
--
ALTER TABLE `topic_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_syllabus` (`syllabus_id`,`user_id`),
  ADD KEY `syllabus_id` (`syllabus_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_login` (`role`,`DESC`);

--
-- Indexes for table `weak_topics`
--
ALTER TABLE `weak_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `syllabus_id` (`syllabus_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `custom_topic_summaries`
--
ALTER TABLE `custom_topic_summaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `syllabus`
--
ALTER TABLE `syllabus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `topic_summaries`
--
ALTER TABLE `topic_summaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `weak_topics`
--
ALTER TABLE `weak_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `custom_topic_summaries`
--
ALTER TABLE `custom_topic_summaries`
  ADD CONSTRAINT `fk_custom_summary_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabus` (`id`);

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_results_syllabus` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `syllabus`
--
ALTER TABLE `syllabus`
  ADD CONSTRAINT `syllabus_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `topic_summaries`
--
ALTER TABLE `topic_summaries`
  ADD CONSTRAINT `fk_summary_syllabus` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_summary_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weak_topics`
--
ALTER TABLE `weak_topics`
  ADD CONSTRAINT `weak_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `weak_topics_ibfk_2` FOREIGN KEY (`syllabus_id`) REFERENCES `syllabus` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
