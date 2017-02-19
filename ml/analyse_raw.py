import pandas as pd
import matplotlib.pyplot as plt
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
from sklearn.linear_model import LinearRegression
from sklearn.cross_validation import train_test_split
from sklearn.metrics import mean_squared_error

results = pd.read_csv("../testing/output/season_1_dataset_1487386080.csv")

# check correlation
corr_analysis = results.corr()["home_points"]