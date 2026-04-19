<?php

namespace App\Controllers;

use App\Models\Collection;
use App\Models\Document;
use App\Models\ActivityLog;
use App\Services\Logger;

class CollectionController extends BaseController
{
    private $collectionModel;
    private $documentModel;
    private $activityModel;
    private $logger;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $this->collectionModel = new Collection();
        $this->documentModel = new Document();
        $this->activityModel = new ActivityLog();
        $this->logger = new Logger();
    }

    /**
     * List user's collections
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        $collections = $this->collectionModel->getUserCollections($userId);

        $this->render('collections.index', [
            'title' => 'My Collections',
            'collections' => $collections,
        ]);
    }

    /**
     * Get user's collections as JSON (for modals/selectors)
     */
    public function listJson(): void
    {
        $userId = $_SESSION['user_id'];
        $collections = $this->collectionModel->getUserCollections($userId);

        $this->json([
            'success' => true,
            'collections' => $collections,
        ]);
    }

    /**
     * View collection with documents
     */
    public function view(int $id, int $page = 1): void
    {
        $userId = $_SESSION['user_id'];
        $collection = $this->collectionModel->getCollectionWithDocuments($id, $userId);

        if (!$collection) {
            $this->abort(404);
        }

        $limit = 12;
        $offset = ($page - 1) * $limit;

        $documents = $this->collectionModel->getCollectionDocuments($id, $limit, $offset);
        $total = $this->collectionModel->countCollectionDocuments($id);
        $totalPages = ceil($total / $limit);

        $this->render('collections.view', [
            'title' => 'Collection: ' . $collection['name'],
            'collection' => $collection,
            'documents' => $documents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Create new collection
     */
    public function create(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $name = $input['name'] ?? null;
        $description = $input['description'] ?? null;
        $color = $input['color'] ?? '#007bff';

        if (empty($name)) {
            $this->json(['success' => false, 'error' => 'Collection name is required'], 400);
        }

        try {
            $collectionId = $this->collectionModel->insert([
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'color' => $color,
            ]);

            $this->activityModel->logActivity($userId, 'create_collection', 'collection', $collectionId);
            $this->logger->info("Collection created: ID=$collectionId");

            $this->json(['success' => true, 'collection_id' => $collectionId]);
        } catch (\Exception $e) {
            $this->logger->error("Create collection failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add document to collection
     */
    public function addDocument(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $collectionId = $input['collection_id'] ?? null;
        $documentId = $input['document_id'] ?? null;

        if (!$collectionId || !$documentId) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        try {
            // Verify collection belongs to user
            $collection = $this->collectionModel->getCollectionWithDocuments($collectionId, $userId);
            if (!$collection) {
                $this->json(['success' => false, 'error' => 'Collection not found'], 404);
            }

            // Verify document belongs to user
            $document = $this->documentModel->findByIdAndUser($documentId, $userId);
            if (!$document) {
                $this->json(['success' => false, 'error' => 'Document not found'], 404);
            }

            $success = $this->collectionModel->addDocumentToCollection($collectionId, $documentId);

            if ($success) {
                $this->activityModel->logActivity($userId, 'add_to_collection', 'document', $documentId);
                $this->json(['success' => true, 'message' => 'Document added to collection']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to add document to collection'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Add to collection failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove document from collection
     */
    public function removeDocument(): void
    {
        $userId = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $collectionId = $input['collection_id'] ?? null;
        $documentId = $input['document_id'] ?? null;

        if (!$collectionId || !$documentId) {
            $this->json(['success' => false, 'error' => 'Missing required fields'], 400);
        }

        try {
            // Verify collection belongs to user
            $collection = $this->collectionModel->getCollectionWithDocuments($collectionId, $userId);
            if (!$collection) {
                $this->json(['success' => false, 'error' => 'Collection not found'], 404);
            }

            $success = $this->collectionModel->removeDocumentFromCollection($collectionId, $documentId);

            if ($success) {
                $this->activityModel->logActivity($userId, 'remove_from_collection', 'document', $documentId);
                $this->json(['success' => true, 'message' => 'Document removed from collection']);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to remove document'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error("Remove from collection failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete collection
     */
    public function delete(int $id): void
    {
        $userId = $_SESSION['user_id'];
        $collection = $this->collectionModel->getCollectionWithDocuments($id, $userId);

        if (!$collection) {
            $this->json(['success' => false, 'error' => 'Collection not found'], 404);
        }

        try {
            $this->collectionModel->delete($id);
            $this->activityModel->logActivity($userId, 'delete_collection', 'collection', $id);
            $this->logger->info("Collection deleted: ID=$id");

            $this->json(['success' => true, 'message' => 'Collection deleted']);
        } catch (\Exception $e) {
            $this->logger->error("Delete collection failed: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
