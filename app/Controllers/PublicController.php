<?php

namespace App\Controllers;

use App\Models\Document;

/**
 * Handles unauthenticated (public) access to approved documents.
 * NO authentication check — intentionally open.
 */
class PublicController extends BaseController
{
    private Document $documentModel;

    public function __construct()
    {
        $this->documentModel = new Document();
    }

    /**
     * Browse approved public documents with search & filters.
     */
    public function library(): void
    {
        $search     = trim($_GET['q'] ?? '');
        $typeFilter = $_GET['type'] ?? '';
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $limit      = 12;
        $offset     = ($page - 1) * $limit;

        $documents = $this->documentModel->getApprovedPublicDocuments(
            $limit, $offset, $search, $typeFilter
        );
        $total = $this->documentModel->countApprovedPublic($search, $typeFilter);
        $totalPages = max(1, ceil($total / $limit));

        $this->render('public.library', [
            'title'       => 'Document Library',
            'documents'   => $documents,
            'search'      => $search,
            'typeFilter'  => $typeFilter,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'isLoggedIn'  => isset($_SESSION['user_id']),
        ], 'public');
    }

    /**
     * View a single approved+public document (read-only, no AI chat for guests)
     */
    public function viewDocument(int $id): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 'guest';

        $document = $this->documentModel->findByIdWithVisibility($id, $userId, $userRole);

        if (!$document || ($document['status'] !== 'approved' || !$document['is_public'])) {
            $this->abort(404);
            return;
        }

        // Increment view count
        $this->documentModel->update($id, [
            'view_count'    => ($document['view_count'] ?? 0) + 1,
            'last_accessed' => date('Y-m-d H:i:s'),
        ]);

        $keywords = json_decode($document['keywords'] ?? '[]', true);

        $this->render('public.document', [
            'title'      => $document['original_name'],
            'document'   => $document,
            'keywords'   => is_array($keywords) ? $keywords : [],
            'isLoggedIn' => isset($_SESSION['user_id']),
        ], 'public');
    }
}
