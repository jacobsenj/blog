<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\AgencyPack\Blog\Controller;

use Psr\Http\Message\ResponseInterface;
use T3G\AgencyPack\Blog\Domain\Model\Comment;
use T3G\AgencyPack\Blog\Domain\Repository\CommentRepository;
use T3G\AgencyPack\Blog\Domain\Repository\PostRepository;
use T3G\AgencyPack\Blog\Service\CacheService;
use T3G\AgencyPack\Blog\Service\SetupService;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[AsController]
class BackendController extends ActionController
{
    public function __construct(protected PostRepository $postRepository, protected CommentRepository $commentRepository, protected ModuleTemplateFactory $moduleTemplateFactory, protected PageRenderer $pageRenderer, protected SetupService $setupService, protected CacheService $cacheService)
    {
    }

    #[\Override]
    public function initializeAction(): void
    {
        //        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Tooltip');
        $this->pageRenderer->addCssFile('EXT:blog/Resources/Public/Css/backend.min.css', 'stylesheet', 'all', '', false);
    }

    public function initializeSetupWizardAction(): void
    {
        $this->initializeDataTables();
        //        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Blog/SetupWizard');
    }

    public function initializePostsAction(): void
    {
        $this->initializeDataTables();
    }

    public function initializeCommentsAction(): void
    {
        $this->initializeDataTables();
        //        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Blog/MassUpdate');
    }

    protected function initializeDataTables(): void
    {
        //        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Blog/Datatables');
        $this->pageRenderer->addCssFile('EXT:blog/Resources/Public/Css/Datatables.min.css', 'stylesheet', 'all', '', false);
    }

    public function setupWizardAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'blogSetups' => $this->setupService->determineBlogSetups(),
        ]);
        return $moduleTemplate->renderResponse('Backend/SetupWizard');
    }

    public function postsAction(int $blogSetup = null): ResponseInterface
    {
        $query = $this->postRepository->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true);
        $this->postRepository->setDefaultQuerySettings($querySettings);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'blogSetups' => $this->setupService->determineBlogSetups(),
            'activeBlogSetup' => $blogSetup,
            'posts' => $this->postRepository->findAllByPid($blogSetup),
        ]);
        return $moduleTemplate->renderResponse('Backend/Posts');
    }

    public function commentsAction(string $filter = null, int $blogSetup = null): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'activeFilter' => $filter,
            'activeBlogSetup' => $blogSetup,
            'commentCounts' => [
                'all' => $this->commentRepository->findAllByFilter(null, $blogSetup)->count(),
                'pending' => $this->commentRepository->findAllByFilter('pending', $blogSetup)->count(),
                'approved' => $this->commentRepository->findAllByFilter('approved', $blogSetup)->count(),
                'declined' => $this->commentRepository->findAllByFilter('declined', $blogSetup)->count(),
                'deleted' => $this->commentRepository->findAllByFilter('deleted', $blogSetup)->count(),
            ],
            'blogSetups' => $this->setupService->determineBlogSetups(),
            'comments' => $this->commentRepository->findAllByFilter($filter, $blogSetup),
        ]);

        return $moduleTemplate->renderResponse('Backend/Posts');
    }

    public function updateCommentStatusAction(string $status, string $filter = null, int $blogSetup = null, array $comments = [], int $comment = null): ResponseInterface
    {
        if ($comment !== null) {
            $comments['__identity'][] = $comment;
        }
        foreach ($comments['__identity'] as $commentId) {
            /** @var Comment|null $comment */
            $comment = $this->commentRepository->findByUid((int)$commentId);
            if ($comment !== null) {
                $updateComment = true;
                switch ($status) {
                    case 'approve':
                        $comment->setStatus(Comment::STATUS_APPROVED);
                        break;
                    case 'decline':
                        $comment->setStatus(Comment::STATUS_DECLINED);
                        break;
                    case 'delete':
                        $comment->setStatus(Comment::STATUS_DELETED);
                        break;
                    default:
                        $updateComment = false;
                }
                if ($updateComment) {
                    $this->commentRepository->update($comment);
                    $this->cacheService->flushCacheByTag('tx_blog_comment_' . $comment->getUid());
                }
            }
        }

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('comments', ['filter' => $filter, 'blogSetup' => $blogSetup]));
    }

    public function createBlogAction(array $data = null): ResponseInterface
    {
        if ($data !== null && $this->setupService->createBlogSetup($data)) {
            $this->addFlashMessage('Your blog setup has been created.', 'Congratulation');
        } else {
            $this->addFlashMessage('Sorry, your blog setup could not be created.', 'An error occurred', ContextualFeedbackSeverity::ERROR);
        }

        return new RedirectResponse($this->uriBuilder->reset()->uriFor('setupWizard'));
    }
}
