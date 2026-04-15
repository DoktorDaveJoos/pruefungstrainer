import ExamController from './ExamController'
import PracticeController from './PracticeController'
import Settings from './Settings'

const Controllers = {
    ExamController: Object.assign(ExamController, ExamController),
    PracticeController: Object.assign(PracticeController, PracticeController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers