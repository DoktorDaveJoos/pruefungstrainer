import HomeController from './HomeController'
import ExamController from './ExamController'
import CheckoutController from './CheckoutController'
import DashboardController from './DashboardController'
import PracticeController from './PracticeController'
import Admin from './Admin'
import Settings from './Settings'

const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
    ExamController: Object.assign(ExamController, ExamController),
    CheckoutController: Object.assign(CheckoutController, CheckoutController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    PracticeController: Object.assign(PracticeController, PracticeController),
    Admin: Object.assign(Admin, Admin),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers