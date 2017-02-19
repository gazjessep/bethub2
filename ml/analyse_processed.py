import pandas as pd
import matplotlib.pyplot as plt
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
from sklearn.linear_model import LinearRegression
from sklearn.cross_validation import train_test_split
from sklearn.metrics import mean_squared_error

results = pd.read_csv("testing_results.csv")

# plt.hist(results["total_ratio"])
# plt.show()

# check correlation
# corr_analysis = results.corr()["total_ratio"]

# Get all the columns from the dataframe.
columns = results.columns.tolist()
# Filter the columns to remove ones we don't want.
columns = [c for c in columns if c not in ["home_ratio", "draw_ratio", "draw_ratio"]]

# Store the variable we'll be predicting on.
target = "total_ratio"

# Generate the training set.  Set random_state to be able to replicate results.
train = results.sample(frac=0.8, random_state=1)
# Select anything not in the training set and put it in the testing set.
test = results.loc[~results.index.isin(train.index)]

# Initialize the model class.
model = LinearRegression()
# Fit the model to the training data.
model.fit(train[columns], train[target])

# Generate our predictions for the test set.
predictions = model.predict(test[columns])

# Compute error between our test predictions and the actual values.
mse = mean_squared_error(predictions, test[target])

print(mse)