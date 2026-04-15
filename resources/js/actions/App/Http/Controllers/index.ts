import ExamController from './ExamController'
import Settings from './Settings'

const Controllers = {
    ExamController: Object.assign(ExamController, ExamController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers