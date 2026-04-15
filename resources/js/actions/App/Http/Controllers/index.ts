import HomeController from './HomeController'
import ExamController from './ExamController'
import CheckoutController from './CheckoutController'
import PracticeController from './PracticeController'
import Settings from './Settings'

const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
    ExamController: Object.assign(ExamController, ExamController),
    CheckoutController: Object.assign(CheckoutController, CheckoutController),
    PracticeController: Object.assign(PracticeController, PracticeController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers