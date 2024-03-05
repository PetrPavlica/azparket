<?php declare(strict_types = 1);

namespace App\Model\Database;

use App\Model\Database\Entity\Absence;
use App\Model\Database\Entity\AbsenceReason;
use App\Model\Database\Entity\AbsenceState;
use App\Model\Database\Entity\ApiClient;
use App\Model\Database\Entity\Configurator;
use App\Model\Database\Entity\ConfiguratorNode;
use App\Model\Database\Entity\ConfiguratorInput;
use App\Model\Database\Entity\ConfiguratorNodeRelation;
use App\Model\Database\Entity\ConfiguratorNodeProduct;
use App\Model\Database\Entity\Currency;
use App\Model\Database\Entity\Customer;
use App\Model\Database\Entity\CustomerInType;
use App\Model\Database\Entity\CustomerNotification;
use App\Model\Database\Entity\CustomerOrdered;
use App\Model\Database\Entity\CustomerState;
use App\Model\Database\Entity\CustomerType;
use App\Model\Database\Entity\Article;
use App\Model\Database\Entity\ArticleDefault;
use App\Model\Database\Entity\ArticleFile;
use App\Model\Database\Entity\ArticleFileInLanguage;
use App\Model\Database\Entity\ArticleImage;
use App\Model\Database\Entity\ArticleInMenu;
use App\Model\Database\Entity\ArticleNew;
use App\Model\Database\Entity\ArticleEvent;
use App\Model\Database\Entity\ArticleTemplate;
use App\Model\Database\Entity\Approve;
use App\Model\Database\Entity\ApproveState;
use App\Model\Database\Entity\ApproveNorm;
use App\Model\Database\Entity\ApprovePart;
use App\Model\Database\Entity\ApproveTime;
use App\Model\Database\Entity\ApproveDocument;
use App\Model\Database\Entity\ApprovePartDocument;
use App\Model\Database\Entity\Banner;
use App\Model\Database\Entity\BannerLanguage;
use App\Model\Database\Entity\BannerPartner;
use App\Model\Database\Entity\BannerPartnerLanguage;
use App\Model\Database\Entity\DeliveryPrice;
use App\Model\Database\Entity\Department;
use App\Model\Database\Entity\Document;
use App\Model\Database\Entity\Employment;
use App\Model\Database\Entity\ExternServiceVisit;
use App\Model\Database\Entity\Field;
use App\Model\Database\Entity\ItemInProcess;
use App\Model\Database\Entity\ItemType;
use App\Model\Database\Entity\ItemTypeInItem;
use App\Model\Database\Entity\Inquiry;
use App\Model\Database\Entity\InquiryProduct;
use App\Model\Database\Entity\InquiryValue;
use App\Model\Database\Entity\Language;
use App\Model\Database\Entity\MachineInExternServiceVisit;
use App\Model\Database\Entity\Machine;
use App\Model\Database\Entity\ManagedChange;
use App\Model\Database\Entity\ManagedChangeStep;
use App\Model\Database\Entity\ManagedRisc;
use App\Model\Database\Entity\ManagedRiscRevaluation;
use App\Model\Database\Entity\Material;
use App\Model\Database\Entity\MaterialGroup;
use App\Model\Database\Entity\MaterialNeedBuy;
use App\Model\Database\Entity\MaterialOnVisit;
use App\Model\Database\Entity\MaterialStock;
use App\Model\Database\Entity\Menu;
use App\Model\Database\Entity\MenuLanguage;
use App\Model\Database\Entity\Offer;
use App\Model\Database\Entity\OfferPart;
use App\Model\Database\Entity\OfferPartTemplate;
use App\Model\Database\Entity\OfferProduct;
use App\Model\Database\Entity\OperationLog;
use App\Model\Database\Entity\OperationLogItem;
use App\Model\Database\Entity\OperationLogProblem;
use App\Model\Database\Entity\OperationLogSuggestion;
use App\Model\Database\Entity\PermissionGroup;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\PermissionRule;
use App\Model\Database\Entity\Process;
use App\Model\Database\Entity\ProcessState;
use App\Model\Database\Entity\Product;
use App\Model\Database\Entity\ProductFile;
use App\Model\Database\Entity\ProductImage;
use App\Model\Database\Entity\ProductInMenu;
use App\Model\Database\Entity\ProductLanguage;
use App\Model\Database\Entity\ProductionLine;
use App\Model\Database\Entity\ProductionPlan;
use App\Model\Database\Entity\ProductionProgressReportSetting;
use App\Model\Database\Entity\ProductionSetting;
use App\Model\Database\Entity\ProductInPlan;
use App\Model\Database\Entity\Qualification;
use App\Model\Database\Entity\Reservation;
use App\Model\Database\Entity\ReservationItem;
use App\Model\Database\Entity\ReservationPlan;
use App\Model\Database\Entity\ReservationProduct;
use App\Model\Database\Entity\Translation;
use App\Model\Database\Entity\Service;
use App\Model\Database\Entity\Setting;
use App\Model\Database\Entity\ShiftPlan;
use App\Model\Database\Entity\ShiftBonus;
use App\Model\Database\Entity\ShiftBonusGroup;
use App\Model\Database\Entity\ShiftBonusTemplate;
use App\Model\Database\Entity\Skill;
use App\Model\Database\Entity\SkillInWorker;
use App\Model\Database\Entity\SkillInWorkerPosition;
use App\Model\Database\Entity\SkillInWorkerTender;
use App\Model\Database\Entity\SkillType;
use App\Model\Database\Entity\Task;
use App\Model\Database\Entity\TaskComment;
use App\Model\Database\Entity\TaskDocument;
use App\Model\Database\Entity\TaskLog;
use App\Model\Database\Entity\TaskState;
use App\Model\Database\Entity\Traffic;
use App\Model\Database\Entity\User;
use App\Model\Database\Entity\UserInWorkplace;
use App\Model\Database\Entity\Vacation;
use App\Model\Database\Entity\VacationFund;
use App\Model\Database\Entity\VacationType;
use App\Model\Database\Entity\Vat;
use App\Model\Database\Entity\Visit;
use App\Model\Database\Entity\VisitDocument;
use App\Model\Database\Entity\VisitLog;
use App\Model\Database\Entity\VisitProcess;
use App\Model\Database\Entity\VisitProcessState;
use App\Model\Database\Entity\VisitState;
use App\Model\Database\Entity\VisitStatus;
use App\Model\Database\Entity\WebSetting;
use App\Model\Database\Entity\WebSettingLanguage;
use App\Model\Database\Entity\Worker;
use App\Model\Database\Entity\WorkerInPlan;
use App\Model\Database\Entity\WorkerInUser;
use App\Model\Database\Entity\WorkerInWorkerTender;
use App\Model\Database\Entity\WorkerNote;
use App\Model\Database\Entity\WorkerOnTraffic;
use App\Model\Database\Entity\WorkerOnTrafficSubstitute;
use App\Model\Database\Entity\WorkerOnVisit;
use App\Model\Database\Entity\WorkerOnVisitProcess;
use App\Model\Database\Entity\WorkerPositionInWorkplace;
use App\Model\Database\Entity\WorkerPosition;
use App\Model\Database\Entity\WorkerPositionSuperiority;
use App\Model\Database\Entity\WorkerStep;
use App\Model\Database\Entity\WorkerTender;
use App\Model\Database\Entity\Workplace;
use App\Model\Database\Entity\WorkplaceSuperiority;
use App\Model\Database\Repository\AbsenceReasonRepository;
use App\Model\Database\Repository\AbsenceRepository;
use App\Model\Database\Repository\AbsenceStateRepository;
use App\Model\Database\Repository\ApiClientRepository;
use App\Model\Database\Repository\ConfiguratorRepository;
use App\Model\Database\Repository\ConfiguratorNodeRepository;
use App\Model\Database\Repository\ConfiguratorInputRepository;
use App\Model\Database\Repository\ConfiguratorNodeRelationRepository;
use App\Model\Database\Repository\ConfiguratorNodeProductRepository;
use App\Model\Database\Repository\CurrencyRepository;
use App\Model\Database\Repository\CustomerInTypeRepository;
use App\Model\Database\Repository\CustomerNotificationRepository;
use App\Model\Database\Repository\CustomerOrderedRepository;
use App\Model\Database\Repository\CustomerRepository;
use App\Model\Database\Repository\CustomerStateRepository;
use App\Model\Database\Repository\CustomerTypeRepository;
use App\Model\Database\Repository\ArticleRepository;
use App\Model\Database\Repository\ArticleDefaultRepository;
use App\Model\Database\Repository\ArticleFileRepository;
use App\Model\Database\Repository\ArticleFileInLanguageRepository;
use App\Model\Database\Repository\ArticleImageRepository;
use App\Model\Database\Repository\ArticleInMenuRepository;
use App\Model\Database\Repository\ArticleNewRepository;
use App\Model\Database\Repository\ArticleEventRepository;
use App\Model\Database\Repository\ArticleTemplateRepository;
use App\Model\Database\Repository\ApproveRepository;
use App\Model\Database\Repository\ApproveStateRepository;
use App\Model\Database\Repository\ApproveNormRepository;
use App\Model\Database\Repository\ApprovePartRepository;
use App\Model\Database\Repository\ApproveTimeRepository;
use App\Model\Database\Repository\ApproveDocumentRepository;
use App\Model\Database\Repository\ApprovePartDocumentRepository;
use App\Model\Database\Repository\BannerRepository;
use App\Model\Database\Repository\BannerLanguageRepository;
use App\Model\Database\Repository\BannerPartnerRepository;
use App\Model\Database\Repository\BannerPartnerLanguageRepository;
use App\Model\Database\Repository\DepartmentRepository;
use App\Model\Database\Repository\DeliveryPriceRepository;
use App\Model\Database\Repository\DocumentRepository;
use App\Model\Database\Repository\EmploymentRepository;
use App\Model\Database\Repository\ExternServiceVisitRepository;
use App\Model\Database\Repository\FieldRepository;
use App\Model\Database\Repository\ItemInProcessRepository;
use App\Model\Database\Repository\ItemTypeRepository;
use App\Model\Database\Repository\ItemTypeInItemRepository;
use App\Model\Database\Repository\InquiryProductRepository;
use App\Model\Database\Repository\InquiryRepository;
use App\Model\Database\Repository\InquiryValueRepository;
use App\Model\Database\Repository\LanguageRepository;
use App\Model\Database\Repository\MachineInExternServiceVisitRepository;
use App\Model\Database\Repository\MachineRepository;
use App\Model\Database\Repository\ManagedChangeRepository;
use App\Model\Database\Repository\ManagedChangeStepRepository;
use App\Model\Database\Repository\ManagedRiscRepository;
use App\Model\Database\Repository\ManagedRiscRevaluationRepository;
use App\Model\Database\Repository\MaterialGroupRepository;
use App\Model\Database\Repository\MaterialNeedBuyRepository;
use App\Model\Database\Repository\MaterialOnVisitRepository;
use App\Model\Database\Repository\MaterialRepository;
use App\Model\Database\Repository\MaterialStockRepository;
use App\Model\Database\Repository\MenuRepository;
use App\Model\Database\Repository\MenuLanguageRepository;
use App\Model\Database\Repository\OfferRepository;
use App\Model\Database\Repository\OfferPartRepository;
use App\Model\Database\Repository\OfferPartTemplateRepository;
use App\Model\Database\Repository\OfferProductRepository;
use App\Model\Database\Repository\OperationLogRepository;
use App\Model\Database\Repository\OperationLogItemRepository;
use App\Model\Database\Repository\OperationLogProblemRepository;
use App\Model\Database\Repository\OperationLogSuggestionRepository;
use App\Model\Database\Repository\PermissionGroupRepository;
use App\Model\Database\Repository\PermissionItemRepository;
use App\Model\Database\Repository\PermissionRuleRepository;
use App\Model\Database\Repository\ProcessRepository;
use App\Model\Database\Repository\ProcessStateRepository;
use App\Model\Database\Repository\ProductFileRepository;
use App\Model\Database\Repository\ProductImageRepository;
use App\Model\Database\Repository\ProductInMenuRepository;
use App\Model\Database\Repository\ProductLanguageRepository;
use App\Model\Database\Repository\ProductionLineRepository;
use App\Model\Database\Repository\ProductionPlanRepository;
use App\Model\Database\Repository\ProductionProgressReportSettingRepository;
use App\Model\Database\Repository\ProductionSettingRepository;
use App\Model\Database\Repository\ProductRepository;
use App\Model\Database\Repository\ProductInPlanRepository;
use App\Model\Database\Repository\QualificationRepository;
use App\Model\Database\Repository\ReservationRepository;
use App\Model\Database\Repository\ReservationItemRepository;
use App\Model\Database\Repository\ReservationPlanRepository;
use App\Model\Database\Repository\ReservationProductRepository;
use App\Model\Database\Repository\ServiceRepository;
use App\Model\Database\Repository\SettingRepository;
use App\Model\Database\Repository\ShiftPlanRepository;
use App\Model\Database\Repository\ShiftBonusRepository;
use App\Model\Database\Repository\ShiftBonusGroupRepository;
use App\Model\Database\Repository\ShiftBonusTemplateRepository;
use App\Model\Database\Repository\SkillRepository;
use App\Model\Database\Repository\SkillInWorkerPositionRepository;
use App\Model\Database\Repository\SkillInWorkerRepository;
use App\Model\Database\Repository\SkillInWorkerTenderRepository;
use App\Model\Database\Repository\SkillTypeRepository;
use App\Model\Database\Repository\TaskCommentRepository;
use App\Model\Database\Repository\TaskDocumentRepository;
use App\Model\Database\Repository\TaskLogRepository;
use App\Model\Database\Repository\TaskRepository;
use App\Model\Database\Repository\TaskStateRepository;
use App\Model\Database\Repository\TrafficRepository;
use App\Model\Database\Repository\TranslationRepository;
use App\Model\Database\Repository\UserInWorkplaceRepository;
use App\Model\Database\Repository\UserRepository;
use App\Model\Database\Repository\VacationRepository;
use App\Model\Database\Repository\VacationFundRepository;
use App\Model\Database\Repository\VacationTypeRepository;
use App\Model\Database\Repository\VatRepository;
use App\Model\Database\Repository\VisitDocumentRepository;
use App\Model\Database\Repository\VisitLogRepository;
use App\Model\Database\Repository\VisitProcessRepository;
use App\Model\Database\Repository\VisitProcessStateRepository;
use App\Model\Database\Repository\VisitRepository;
use App\Model\Database\Repository\VisitStateRepository;
use App\Model\Database\Repository\VisitStatusRepository;
use App\Model\Database\Repository\WebSettingRepository;
use App\Model\Database\Repository\WebSettingLanguageRepository;
use App\Model\Database\Repository\WorkerInPlanRepository;
use App\Model\Database\Repository\WorkerInUserRepository;
use App\Model\Database\Repository\WorkerInWorkerTenderRepository;
use App\Model\Database\Repository\WorkerNoteRepository;
use App\Model\Database\Repository\WorkerOnTrafficRepository;
use App\Model\Database\Repository\WorkerOnTrafficSubstituteRepository;
use App\Model\Database\Repository\WorkerOnVisitProcessRepository;
use App\Model\Database\Repository\WorkerOnVisitRepository;
use App\Model\Database\Repository\WorkerPositionInWorkplaceRepository;
use App\Model\Database\Repository\WorkerPositionRepository;
use App\Model\Database\Repository\WorkerPositionSuperiorityRepository;
use App\Model\Database\Repository\WorkerRepository;
use App\Model\Database\Repository\WorkerStepRepository;
use App\Model\Database\Repository\WorkerTenderRepository;
use App\Model\Database\Repository\WorkplaceRepository;
use App\Model\Database\Repository\WorkplaceSuperiorityRepository;

/**
 * @mixin EntityManager
 */
trait TRepositories
{

    public function getUserRepository(): UserRepository
    {
        return $this->getRepository(User::class);
    }

    public function getUserInWorkplaceRepository(): UserInWorkplaceRepository
    {
        return $this->getRepository(UserInWorkplace::class);
    }

    public function getArticleRepository(): ArticleRepository
    {
        return $this->getRepository(Article::class);
    }

    public function getArticleDefaultRepository(): ArticleDefaultRepository
    {
        return $this->getRepository(ArticleDefault::class);
    }

    public function getArticleFileRepository(): ArticleFileRepository
    {
        return $this->getRepository(ArticleFile::class);
    }

    public function getArticleFileInLanguageRepository(): ArticleFileInLanguageRepository
    {
        return $this->getRepository(ArticleFileInLanguage::class);
    }

    public function getArticleImageRepository(): ArticleImageRepository
    {
        return $this->getRepository(ArticleImage::class);
    }

    public function getArticleInMenuRepository(): ArticleInMenuRepository
    {
        return $this->getRepository(ArticleInMenu::class);
    }

    public function getArticleNewRepository(): ArticleNewRepository
    {
        return $this->getRepository(ArticleNew::class);
    }

    public function getArticleEventRepository(): ArticleEventRepository
    {
        return $this->getRepository(ArticleEvent::class);
    }

    public function getArticleTemplateRepository(): ArticleTemplateRepository
    {
        return $this->getRepository(ArticleTemplate::class);
    }

    public function getApproveRepository(): ApproveRepository
    {
        return $this->getRepository(Approve::class);
    }

    public function getApproveStateRepository(): ApproveStateRepository
    {
        return $this->getRepository(ApproveState::class);
    }

    public function getApproveNormRepository(): ApproveNormRepository
    {
        return $this->getRepository(ApproveNorm::class);
    }

    public function getApprovePartRepository(): ApprovePartRepository
    {
        return $this->getRepository(ApprovePart::class);
    }

    public function getApproveTimeRepository(): ApproveTimeRepository
    {
        return $this->getRepository(ApproveTime::class);
    }

    public function getApproveDocumentRepository(): ApproveDocumentRepository
    {
        return $this->getRepository(ApproveDocument::class);
    }

    public function getApprovePartDocumentRepository(): ApprovePartDocumentRepository
    {
        return $this->getRepository(ApprovePartDocument::class);
    }

    public function getBannerRepository(): BannerRepository
    {
        return $this->getRepository(Banner::class);
    }
    
    public function getBannerLanguageRepository(): BannerLanguageRepository
    {
        return $this->getRepository(BannerLanguage::class);
    }

    public function getBannerPartnerRepository(): BannerPartnerRepository
    {
        return $this->getRepository(BannerPartner::class);
    }
    
    public function getBannerPartnerLanguageRepository(): BannerPartnerLanguageRepository
    {
        return $this->getRepository(BannerPartnerLanguage::class);
    }
    
    public function getLanguageRepository(): LanguageRepository
    {
        return $this->getRepository(Language::class);
    }
    
    public function getMenuRepository(): MenuRepository
    {
        return $this->getRepository(Menu::class);
    }
    
    public function getMenuLanguageRepository(): MenuLanguageRepository
    {
        return $this->getRepository(MenuLanguage::class);
    }
    
    public function getTranslationRepository(): TranslationRepository
    {
        return $this->getRepository(Translation::class);
    }
    
    public function getWebSettingRepository(): WebSettingRepository
    {
        return $this->getRepository(WebSetting::class);
    }
    
    public function getWebSettingLanguageRepository(): WebSettingLanguageRepository
    {
        return $this->getRepository(WebSettingLanguage::class);
    }

    public function getVacationRepository(): VacationRepository
    {
        return $this->getRepository(Vacation::class);
    }

    public function getVacationFundRepository(): VacationFundRepository
    {
        return $this->getRepository(VacationFund::class);
    }

    public function getVacationTypeRepository(): VacationTypeRepository
    {
        return $this->getRepository(VacationType::class);
    }

    public function getVatRepository(): VatRepository
    {
        return $this->getRepository(Vat::class);
    }

    public function getWorkerRepository(): WorkerRepository
    {
        return $this->getRepository(Worker::class);
    }

    public function getWorkerInPlanRepository(): WorkerInPlanRepository
    {
        return $this->getRepository(WorkerInPlan::class);
    }

    public function getWorkerPositionRepository(): WorkerPositionRepository
    {
        return $this->getRepository(WorkerPosition::class);
    }

    public function getWorkerPositionSuperiorityRepository(): WorkerPositionSuperiorityRepository
    {
        return $this->getRepository(WorkerPositionSuperiority::class);
    }

    public function getWorkerTenderRepository(): WorkerTenderRepository
    {
        return $this->getRepository(WorkerTender::class);
    }

    public function getWorkerNoteRepository(): WorkerNoteRepository
    {
        return $this->getRepository(WorkerNote::class);
    }

    public function getSkillRepository(): SkillRepository
    {
        return $this->getRepository(Skill::class);
    }

    public function getSkillInWorkerRepository(): SkillInWorkerRepository
    {
        return $this->getRepository(SkillInWorker::class);
    }

    public function getSkillInWorkerPositionRepository(): SkillInWorkerPositionRepository
    {
        return $this->getRepository(SkillInWorkerPosition::class);
    }
    
    public function getWorkerStepRepository(): WorkerStepRepository
    {
        return $this->getRepository(WorkerStep::class);
    }

    public function getSkillInWorkerTenderRepository(): SkillInWorkerTenderRepository
    {
        return $this->getRepository(SkillInWorkerTender::class);
    }

    public function getSkillTypeRepository(): SkillTypeRepository
    {
        return $this->getRepository(SkillType::class);
    }

    public function getWorkerInWorkerTenderRepository(): WorkerInWorkerTenderRepository
    {
        return $this->getRepository(WorkerInWorkerTender::class);
    }
    
    public function getOfferRepository(): OfferRepository
    {
        return $this->getRepository(Offer::class);
    }

    public function getOfferPartRepository(): OfferPartRepository
    {
        return $this->getRepository(OfferPart::class);
    }

    public function getOfferPartTemplateRepository(): OfferPartTemplateRepository
    {
        return $this->getRepository(OfferPartTemplate::class);
    }

    public function getOfferProductRepository(): OfferProductRepository
    {
        return $this->getRepository(OfferProduct::class);
    }

    public function getOperationLogRepository(): OperationLogRepository
    {
        return $this->getRepository(OperationLog::class);
    }

    public function getOperationLogItemRepository(): OperationLogItemRepository
    {
        return $this->getRepository(OperationLogItem::class);
    }

    public function getOperationLogProblemRepository(): OperationLogProblemRepository
    {
        return $this->getRepository(OperationLogProblem::class);
    }

    public function getOperationLogSuggestionRepository(): OperationLogSuggestionRepository
    {
        return $this->getRepository(OperationLogSuggestion::class);
    }

    public function getProductFileRepository(): ProductFileRepository
    {
        return $this->getRepository(ProductFile::class);
    }

    public function getProductImageRepository(): ProductImageRepository
    {
        return $this->getRepository(ProductImage::class);
    }

    public function getProductInMenuRepository(): ProductInMenuRepository
    {
        return $this->getRepository(ProductInMenu::class);
    }

    public function getProductLanguageRepository(): ProductLanguageRepository
    {
        return $this->getRepository(ProductLanguage::class);
    }

    public function getProductionLineRepository(): ProductionLineRepository
    {
        return $this->getRepository(ProductionLine::class);
    }

    public function getProductionPlanRepository(): ProductionPlanRepository
    {
        return $this->getRepository(ProductionPlan::class);
    }

    public function getProductionSettingRepository(): ProductionSettingRepository
    {
        return $this->getRepository(ProductionSetting::class);
    }

    public function getProductRepository(): ProductRepository
    {
        return $this->getRepository(Product::class);
    }

    public function getProductInPlanRepository(): ProductInPlanRepository
    {
        return $this->getRepository(ProductInPlan::class);
    }
    
    public function getReservationRepository(): ReservationRepository
    {
        return $this->getRepository(Reservation::class);
    }
    
    public function getReservationItemRepository(): ReservationItemRepository
    {
        return $this->getRepository(ReservationItem::class);
    }

    public function getReservationPlanRepository(): ReservationPlanRepository
    {
        return $this->getRepository(ReservationPlan::class);
    }

    public function getReservationProductRepository(): ReservationProductRepository
    {
        return $this->getRepository(ReservationProduct::class);
    }

    public function getWorkerInUserRepository(): WorkerInUserRepository
    {
        return $this->getRepository(WorkerInUser::class);
    }

    public function getWorkerPositionInWorkplaceRepository(): WorkerPositionInWorkplaceRepository
    {
        return $this->getRepository(WorkerPositionInWorkplace::class);
    }
    
    public function getWorkplaceRepository(): WorkplaceRepository
    {
        return $this->getRepository(Workplace::class);
    }

    public function getWorkplaceSuperiorityRepository(): WorkplaceSuperiorityRepository
    {
        return $this->getRepository(WorkplaceSuperiority::class);
    }

    public function getMachineRepository(): MachineRepository
    {
        return $this->getRepository(Machine::class);
    }

    public function getExternServiceVisitRepository(): ExternServiceVisitRepository
    {
        return $this->getRepository(ExternServiceVisit::class);
    }

    public function getManagedChangeRepository(): ManagedChangeRepository
    {
        return $this->getRepository(ManagedChange::class);
    }

    public function getMachineInExternServiceVisitRepository(): MachineInExternServiceVisitRepository
    {
        return $this->getRepository(MachineInExternServiceVisit::class);
    }

    public function getManagedChangeStepRepository(): ManagedChangeStepRepository
    {
        return $this->getRepository(ManagedChangeStep::class);
    }

    public function getManagedRiscRepository(): ManagedRiscRepository
    {
        return $this->getRepository(ManagedRisc::class);
    }

    public function getManagedRiscRevaluationRepository(): ManagedRiscRevaluationRepository
    {
        return $this->getRepository(ManagedRiscRevaluation::class);
    }

    public function getDepartmentRepository(): DepartmentRepository
    {
        return $this->getRepository(Department::class);
    }
    
    public function getDeliveryPriceRepository(): DeliveryPriceRepository
    {
        return $this->getRepository(DeliveryPrice::class);
    }
    
    public function getConfiguratorRepository(): ConfiguratorRepository
    {
        return $this->getRepository(Configurator::class);
    }

    public function getConfiguratorNodeRepository(): ConfiguratorNodeRepository
    {
        return $this->getRepository(ConfiguratorNode::class);
    }

    public function getConfiguratorInputRepository(): ConfiguratorInputRepository
    {
        return $this->getRepository(ConfiguratorInput::class);
    }

    public function getConfiguratorNodeRelationRepository(): ConfiguratorNodeRelationRepository
    {
        return $this->getRepository(ConfiguratorNodeRelation::class);
    }

    public function getConfiguratorNodeProductRepository(): ConfiguratorNodeProductRepository
    {
        return $this->getRepository(ConfiguratorNodeProduct::class);
    }

    public function getCurrencyRepository(): CurrencyRepository
    {
        return $this->getRepository(Currency::class);
    }

    public function getFieldRepository(): FieldRepository
    {
        return $this->getRepository(Field::class);
    }

    public function getDocumentRepository(): DocumentRepository
    {
        return $this->getRepository(Document::class);
    }

    public function getEmploymentRepository(): EmploymentRepository
    {
        return $this->getRepository(Employment::class);
    }

    public function getQualificationRepository(): QualificationRepository
    {
        return $this->getRepository(Qualification::class);
    }

    public function getSettingRepository(): SettingRepository
    {
        return $this->getRepository(Setting::class);
    }

    public function getShiftPlanRepository(): ShiftPlanRepository
    {
        return $this->getRepository(ShiftPlan::class);
    }

    public function getShiftBonusRepository(): ShiftBonusRepository
    {
        return $this->getRepository(ShiftBonus::class);
    }

    public function getShiftBonusGroupRepository(): ShiftBonusGroupRepository
    {
        return $this->getRepository(ShiftBonusGroup::class);
    }

    public function getShiftBonusTemplateRepository(): ShiftBonusTemplateRepository
    {
        return $this->getRepository(ShiftBonusTemplate::class);
    }

    public function getProcessRepository(): ProcessRepository
    {
        return $this->getRepository(Process::class);
    }

    public function getProcessStateRepository(): ProcessStateRepository
    {
        return $this->getRepository(ProcessState::class);
    }

    public function getItemInProcessRepository(): ItemInProcessRepository {
        return $this->getRepository(ItemInProcess::class);
    }

    public function getItemTypeRepository(): ItemTypeRepository {
        return $this->getRepository(ItemType::class);
    }

    public function getItemTypeInItemRepository(): ItemTypeInItemRepository {
        return $this->getRepository(ItemTypeInItem::class);
    }

    public function getInquiryProductRepository(): InquiryProductRepository {
        return $this->getRepository(InquiryProduct::class);
    }
    
    public function getInquiryRepository(): InquiryRepository {
        return $this->getRepository(Inquiry::class);
    }
    
    public function getInquiryValueRepository(): InquiryValueRepository {
        return $this->getRepository(InquiryValue::class);
    }

    public function getCustomerRepository(): CustomerRepository
    {
        return $this->getRepository(Customer::class);
    }

    public function getCustomerInTypeRepository(): CustomerInTypeRepository
    {
        return $this->getRepository(CustomerInType::class);
    }

    public function getCustomerNotificationRepository(): CustomerNotificationRepository
    {
        return $this->getRepository(CustomerNotification::class);
    }

    public function getCustomerOrderedRepository(): CustomerOrderedRepository
    {
        return $this->getRepository(CustomerOrdered::class);
    }

    public function getCustomerStateRepository(): CustomerStateRepository
    {
        return $this->getRepository(CustomerState::class);
    }

    public function getCustomerTypeRepository(): CustomerTypeRepository
    {
        return $this->getRepository(CustomerType::class);
    }

    public function getProductionProgressReportSettingRepository(): ProductionProgressReportSettingRepository
    {
        return $this->getRepository(ProductionProgressReportSetting::class);
    }

    public function getTaskRepository(): TaskRepository
    {
        return $this->getRepository(Task::class);
    }

    public function getTaskLogRepository(): TaskLogRepository
    {
        return $this->getRepository(TaskLog::class);
    }

    public function getTaskStateRepository(): TaskStateRepository
    {
        return $this->getRepository(TaskState::class);
    }

    public function getTaskDocumentRepository(): TaskDocumentRepository
    {
        return $this->getRepository(TaskDocument::class);
    }

    public function getTaskCommentRepository(): TaskCommentRepository
    {
        return $this->getRepository(TaskComment::class);
    }

    public function getPermissionGroupRepository(): PermissionGroupRepository
    {
        return $this->getRepository(PermissionGroup::class);
    }

    public function getPermissionRuleRepository(): PermissionRuleRepository
    {
        return $this->getRepository(PermissionRule::class);
    }

    public function getPermissionItemRepository(): PermissionItemRepository
    {
        return $this->getRepository(PermissionItem::class);
    }

    public function getAbsenceRepository(): AbsenceRepository
    {
        return $this->getRepository(Absence::class);
    }

    public function getAbsenceReasonRepository(): AbsenceReasonRepository
    {
        return $this->getRepository(AbsenceReason::class);
    }

    public function getAbsenceStateRepository(): AbsenceStateRepository
    {
        return $this->getRepository(AbsenceState::class);
    }

    public function getMaterialRepository(): MaterialRepository
    {
        return $this->getRepository(Material::class);
    }

    public function getMaterialGroupRepository(): MaterialGroupRepository
    {
        return $this->getRepository(MaterialGroup::class);
    }

    public function getMaterialNeedBuyRepository(): MaterialNeedBuyRepository
    {
        return $this->getRepository(MaterialNeedBuy::class);
    }

    public function getMaterialOnVisitRepository(): MaterialOnVisitRepository
    {
        return $this->getRepository(MaterialOnVisit::class);
    }

    public function getMaterialStockRepository(): MaterialStockRepository
    {
        return $this->getRepository(MaterialStock::class);
    }

    public function getServiceRepository(): ServiceRepository
    {
        return $this->getRepository(Service::class);
    }

    public function getTrafficRepository(): TrafficRepository
    {
        return $this->getRepository(Traffic::class);
    }

    public function getVisitRepository(): VisitRepository
    {
        return $this->getRepository(Visit::class);
    }

    public function getVisitDocumentRepository(): VisitDocumentRepository
    {
        return $this->getRepository(VisitDocument::class);
    }

    public function getVisitLogRepository(): VisitLogRepository
    {
        return $this->getRepository(VisitLog::class);
    }

    public function getVisitProcessRepository(): VisitProcessRepository
    {
        return $this->getRepository(VisitProcess::class);
    }

    public function getVisitProcessStateRepository(): VisitProcessStateRepository
    {
        return $this->getRepository(VisitProcessState::class);
    }

    public function getVisitStateRepository(): VisitStateRepository
    {
        return $this->getRepository(VisitState::class);
    }

    public function getVisitStatusRepository(): VisitStatusRepository
    {
        return $this->getRepository(VisitStatus::class);
    }

    public function getWorkerOnTrafficRepository(): WorkerOnTrafficRepository
    {
        return $this->getRepository(WorkerOnTraffic::class);
    }

    public function getWorkerOnTrafficSubstituteRepository(): WorkerOnTrafficSubstituteRepository
    {
        return $this->getRepository(WorkerOnTrafficSubstitute::class);
    }

    public function getWorkerOnVisitRepository(): WorkerOnVisitRepository
    {
        return $this->getRepository(WorkerOnVisit::class);
    }

    public function getWorkerOnVisitProcessRepository(): WorkerOnVisitProcessRepository
    {
        return $this->getRepository(WorkerOnVisitProcess::class);
    }

    public function getApiClientRepository(): ApiClientRepository
    {
        return $this->getRepository(ApiClient::class);
    }
}
