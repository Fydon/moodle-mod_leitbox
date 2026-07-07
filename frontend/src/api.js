import axios from 'axios';

let config = {};

export const initApi = (cfg) => {
    config = cfg;
};

export const moodleCall = async (methodname, args) => {
    const url = `${config.wwwroot}/lib/ajax/service.php?sesskey=${config.sesskey}`;
    const payload = [{
        index: 0,
        methodname,
        args
    }];
    const response = await axios.post(url, payload);
    const data = response.data[0];
    if (data.error) {
        throw new Error(data.exception);
    }
    return data.data;
};

export const getCardsByBox = (boxnumber) => {
    return moodleCall('mod_adaptivereview_get_cards_by_box', {
        instanceid: config.instanceid,
        boxnumber
    });
};

export const getBoxCounts = async () => {
    const counts = await moodleCall('mod_adaptivereview_get_box_counts', {
        instanceid: config.instanceid
    });

    const result = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
    for (const item of counts) {
        result[item.box_number] = item.count;
    }
    return result;
};

export const submitAnswer = (cardid, rating) => {
    return moodleCall('mod_adaptivereview_submit_answer', {
        cardid,
        rating
    });
};

export const getLogoUrl = () => {
    return `${config.wwwroot}/mod/adaptivereview/pix/logo.png`;
};

export const resetProgress = (instanceid) => {
    return moodleCall('mod_adaptivereview_reset_progress', { instanceid });
};
